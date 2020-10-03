<?php

namespace App\Controllers;

use App\Entity\Authorization\User;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IEndpointRepository;
use App\Exceptions\InternalErrorException;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\InvalidTypeException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use Closure;
use Doctrine\ORM\ORMException;
use IAuthRepositoryController;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class RepositoryController extends AbstractController implements IAuthRepositoryController
{

	use SortableController, PageableController, DefaultControllerAccessible, FilterableController;

	/** @var IEndpointRepository */
	protected $repository;

	/**
	 * function(Request $request, Response $response, ArgumentParser $args)
	 * @var callable[]
	 */
	protected $beforeRequest = [];


    /** @var array */
	protected $user_permissions;

    /**
     * Returns full class name of entity REPOSITORY that is related to this controller
     * @return string
     */
	abstract protected static function getRepositoryClassName(): string;

    /**
     * Returns short class name of ENTITY that is related to this controller
     * @return string
     */
	abstract protected static function getObjectName(): string;

    /**
     * Returns data prepared for output on DETAIL endpoint(.../{entity}/{id})
     * @param IdentifiedObject $object
     * @return array
     */
	abstract protected function getData(IdentifiedObject $object): array;


	/**
	 * @param array $events
	 * @param array ...$args
	 * @internal
	 */
	protected function runEvents(array $events, ...$args)
	{
		foreach ($events as $event)
			call_user_func_array($event, $args);
	}

    /**
     * @param ArgumentParser $args
     * @return array
     * @throws InvalidTypeException
     */
	protected function getReadIds(ArgumentParser $args): array
	{
		return array_map(function($item) {
			return (int) $item;
		}, explode(',', $args->getString('id')));
	}


	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->repository = $c->get(static::getRepositoryClassName());

        $this->beforeRequest[] = function(Request $request, Response $response, ArgumentParser $args)
        {
            $this->setUserPermissions($request->getAttribute('oauth_user_id'));
        };
	}

    /**
     * This function is responsible for GET action that returns list of objects (e.g. /models).
     * @param Request $request
     * @param Response $response
     * @param ArgumentParser $args
     * @return Response
     * @throws mixed
     */
	public function read(Request $request, Response $response, ArgumentParser $args)
	{
	    $this->runEvents($this->beforeRequest, $request, $response, $args);
        $filter['accessFilter'] = $this->validateList($this->user_permissions);
        $filter['argFilter'] = static::getFilter($args);
        $numResults = $this->repository->getNumResults($filter);
        static::validateFilter($numResults, $filter['argFilter']);
        $limit = static::getPaginationData($args, $numResults);
		$response = $response->withHeader('X-Count', $numResults);
		$response = $response->withHeader('X-Pages', $limit['pages']);
		return self::formatOk($response, $this->repository->getList($filter, self::getSort($args), $limit));
	}

    /**
     * This function is responsible for GET action that returns detail of an object (e.g. /models/20).
     * @param Request $request
     * @param Response $response
     * @param ArgumentParser $args
     * @return Response
     * @throws mixed
     */
	public function readIdentified(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);
        $id = current($this->getReadIds($args));
        $ent = $this->getObject((int)$id);
        $this->validateDetail($this->user_permissions);
        $data = static::getPaginationOnDetail($args, $this->getData($ent));
        //FIXME move this to some other controller, pageable would be the best
        if (array_key_exists('maxCount', $data)){
            $maxCount = $data['maxCount'];
            $response = $response->withHeader('X-MaxCount', $maxCount);
            $response = $response->withHeader('X-Pages', $args['perPage'] ? ceil($maxCount / $args['perPage']) : 1);
            unset($data['maxCount']);
        }
        return self::formatOk($response, $data);
	}


	/**
     * Get an object from a repository, if repository is not provided it uses current repository.
	 * @param int                      $id
	 * @param IEndpointRepository|null $repository
	 * @param string|null              $objectName
	 * @return IdentifiedObject
	 * @throws InternalErrorException
	 * @throws NonExistingObjectException
	 */
	protected function getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
	{
		if (!$repository)
			$repository = $this->repository;
		if (!$objectName)
			$objectName = static::getObjectName();
		try {
			$ent = $repository->get($id);
			if (!$ent)
				throw new NonExistingObjectException($id, $objectName);
		} catch (ORMException $e) {
			throw new InternalErrorException('Failed getting ' . $objectName . ' ID ' . $id, $e);
		}

		return $ent;
	}

    /**
     * Get an object via ID and its entity class name.
     * @param string $entityClassName
     * @param int $id
     * @return IdentifiedObject
     * @throws NonExistingObjectException
     */
	protected function getObjectViaORM(string $entityClassName, int $id){
        /** @var  IdentifiedObject $object */
        $object = $this->orm->getRepository($entityClassName)->find($id);
        if (!$object)
            throw new NonExistingObjectException($id, $entityClassName);
        return $object;
    }


	// ============================================== HELPERS

	protected static function identifierGetter(): Closure
	{
		return function(IdentifiedObject $object) {
			return $object->getId();
		};
	}

    /**
     * @param $id
     */
    public function setUserPermissions($id)
    {
        if (!is_null($id)) {
            $authUser = $this->orm->getRepository(User::class)->find($id);
            /** $usersGroupRoles is an array, where key = GroupId and the value = groupRole in that group */
            $usersGroupRoles = [];
            foreach ($authUser->getGroups()->getIterator() as $groupLink){
                $usersGroupRoles[$groupLink->getuserGroupId()->getId()] = $groupLink->getRoleId();
            }
            $this->user_permissions = ["group_wise" => $usersGroupRoles, "platform_wise" => $authUser->getType(), "user_id" => $id];
        }
        else {
            $this->user_permissions = ["group_wise" => [1 => 10], "platform_wise" => User::GUEST, "user_id" => null];
        }
    }

    /**
     * @param array $user_permissions
     * @return array additional collection filter,
     * key (is group id of users groups) => value (is prepared for dql filter)
     * @throws InvalidArgumentException if user with non-existing role
     */
    public function validateList(array $user_permissions) : ?array
    {
        switch ($user_permissions['platform_wise']){
            case User::ADMIN:
                return [];
            case User::POWER:
            case User::REGISTERED:
            case User::TEMPORARY:
            case User::GUEST:
                $this->hasAccessToObject($user_permissions['group_wise']);
                return $this->getAccessFilter($user_permissions['group_wise']);
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['platform_wise'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @param array $user_permissions
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validateDetail(array $user_permissions) : bool
    {
        switch ($user_permissions['platform_wise']){
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
            case User::TEMPORARY:
            case User::GUEST:
                $this->hasAccessToObject($user_permissions['group_wise']);
                return true;
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

}
