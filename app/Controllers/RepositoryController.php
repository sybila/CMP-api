<?php

namespace App\Controllers;

use App\Entity\Authorization\User;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IEndpointRepository;
use App\Exceptions\InternalErrorException;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\InvalidAuthenticationException;
use App\Exceptions\InvalidRoleException;
use App\Exceptions\InvalidTypeException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use App\Helpers\CMPSQLLogger;
use Closure;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\ORMException;
use IGroupRoleAuthController;
use IPlatformRoleAuthController;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class RepositoryController extends AbstractController
    implements IGroupRoleAuthController, IPlatformRoleAuthController
{

	use ControllerSortable, ControllerPageable, DefaultControllerAccessible, ControllerFilterable;

	/** @var IEndpointRepository */
	protected $repository;

	/**
	 * function(Request $request, Response $response, ArgumentParser $args)
	 * @var callable[]
	 */
	protected $beforeRequest = [];


    /** @var array */
	protected $userPermissions;

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
		$logger = new CMPSQLLogger();
        $this->orm
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger($logger);
        $this->beforeRequest[] = function(Request $request, Response $response, ArgumentParser $args)
        {
            $this->setUserPermissions($request->getAttribute('oauth_user_id'));
            $this->orm->getEventManager()->addEventSubscriber(
                new NotificationDispatchController($this->userPermissions['user_id'])
            );
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
	    $this->permitUser([$this, 'validateList'], [$this, 'canList']);
        $filter['accessFilter'] = $this->getAccessFilter($this->userPermissions['group_wise']);
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
        $this->permitUser([$this, 'validateDetail'], [$this, 'canDetail']);
        $id = current($this->getReadIds($args));
        $ent = $this->getObject((int)$id);
        $data = $this->getData($ent);
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
            $this->userPermissions = ["group_wise" => $usersGroupRoles, "platform_wise" => $authUser->getType()->getTier(), "user_id" => $id];
        }
        else {
            $this->userPermissions = ["group_wise" => [1 => 10], "platform_wise" => User::GUEST, "user_id" => null];
        }
    }

    /**
     * Return (void) if permission is granted.
     * Throw an exception otherwise.
     * @param callable $authPlatformRoleCheck
     * @param callable $authGroupRoleCheck
     * @throws InvalidRoleException
     * @throws NonExistingObjectException
     * @throws InvalidAuthenticationException
     */
    protected function permitUser(callable $authPlatformRoleCheck, callable $authGroupRoleCheck): void
    {
        //Platform role is above the all of the permissions
        if (call_user_func($authPlatformRoleCheck)){
            return;
        }
        //But if platform role is not enought, check if the resource is not available via group
        $groupId = $this->hasAccessToObject($this->userPermissions['group_wise']);
        //If no group ID is returned, the resource is public
        if ($groupId === null){
            return;
        }
        //If the group ID is returned, check if the user has the group permission to perform the action
        if (call_user_func($authGroupRoleCheck,
            $this->userPermissions['group_wise'][$groupId], $this->userPermissions['user_id'])){
            return;
        }
        throw new InvalidRoleException(debug_backtrace()[1]['function'],
            $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }

    /**
     * @return bool additional collection filter,
     * key (is group id of users groups) => value (is prepared for dql filter)
     * @throws InvalidArgumentException if user with non-existing role
     */
    public function validateList(): bool
    {
        switch ($this->userPermissions['platform_wise']){
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
            case User::TEMPORARY:
            case User::GUEST:
                return false;
            default:
                throw new InvalidArgumentException('user_type', $this->userPermissions['platform_wise'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validateDetail(): bool
    {
        switch ($this->userPermissions['platform_wise']){
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
            case User::TEMPORARY:
            case User::GUEST:
                return false;
            default:
                throw new InvalidArgumentException('user_type', $this->userPermissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

}
