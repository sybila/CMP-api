<?php

namespace App\Controllers;

use App\Entity\Authorization\UserGroupToUser;
use App\Entity\Authorization\User;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IEndpointRepository;
use App\Exceptions\EmptySelectionException;
use App\Exceptions\InternalErrorException;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\InvalidAuthenticationException;
use App\Exceptions\InvalidTypeException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class RepositoryController extends AbstractController
{

	use SortableController, PageableController, RepoAccessController, FilterableController;

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

		$server = $c->get(ResourceServer::class);
        $this->beforeRequest[] = function(Request $request, Response $response, ArgumentParser $args) use ($server)
        {
            $this->getAccess($request, $server);
        };
	}

    /**
     * @param Request $request needed for auth.
     //* @return array key 'group_wise' contains array of associative array of key 'groupId' => groupRoleId (tier),
     * key 'platform_wise' contains values from 0 to 4, (according to permissions on the platform)
     * @param ResourceServer $server
     * @throws InvalidAuthenticationException
     */
    public function getAccess(Request $request, ResourceServer $server) //: array
    {
        if ($request->getHeader('http_authorization')){
            try {
                $request = $server->validateAuthenticatedRequest($request);
            } catch (OAuthServerException $e) {
                throw new InvalidAuthenticationException($e->getMessage(), $e->getHint());
            }
            $this->user_permissions = $this->getUserPermissions($request->getAttribute('oauth_user_id'));
        }
        $this->user_permissions = ["group_wise" => [1 => 10], "platform_wise" => 0,  "user_id" => 0];
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
        dump($request->getAttributes());exit;
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

	protected static function identifierGetter(): \Closure
	{
		return function(IdentifiedObject $object) {
			return $object->getId();
		};
	}

    /**
     * @param $id
     * @return array
     */
    public function getUserPermissions($id)
    {
        $authUser = $this->orm->getRepository(User::class)->find($id);
        $usersGroupRoles = $authUser->getGroups()->map(function (UserGroupToUser $groupLink) {
            $group = $groupLink->getUserGroupId();
            return [$group->getId() => (int) $groupLink->getRoleId()];
        })->toArray();
        return ["group_wise" => $usersGroupRoles, "platform_wise" => $authUser->getType(), "user_id" => $id];
    }

    /**
     * @param array $user_permissions
     * @return array additional collection filter,
     * key (is group id of users groups) => value (is prepared for dql filter)
     * @throws InternalErrorException
     * @throws InvalidArgumentException if user with non-existing role
     * @throws InvalidAuthenticationException
     * @throws NonExistingObjectException
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
     * @throws InternalErrorException
     * @throws InvalidArgumentException
     * @throws InvalidAuthenticationException
     * @throws NonExistingObjectException
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
