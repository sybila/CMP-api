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
use App\Exceptions\InvalidRoleException;
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

	/** @var ResourceServer */
	protected $server;

	abstract protected static function getRepositoryClassName(): string;


	abstract protected static function getObjectName(): string;


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
		$this->server = $c->get(ResourceServer::class);
	}

    /**
     * @param Request $request needed for auth.
     * @return array key 'group_wise' contains array of associative array of key 'groupId' => groupRoleId (tier),
     * key 'platform_wise' contains values from 0 to 3, (according to permissions on the platform)
     * @throws InvalidAuthenticationException
     */
    public function getAccess(Request $request): array
    {
        if ($request->getHeader('http_authorization')){
            try {
                $request = $this->server->validateAuthenticatedRequest($request);
            } catch (OAuthServerException $e) {
                throw new InvalidAuthenticationException($e->getMessage(), $e->getHint());
            }
            return $this->getUserPermissions($request->getAttribute('oauth_user_id'));
        }
        return ["group_wise" => [1 => 10], "platform_wise" => 0];
    }

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
	    $this->runEvents($this->beforeRequest, $request, $response, $args);
        $filter['accessFilter'] = $this->validateList($this->getAccess($request));
        $filter['argFilter'] = static::getFilter($args);
        $numResults = $this->repository->getNumResults($filter);
        static::validateFilter($numResults, $filter['argFilter']);
        $limit = static::getPaginationData($args, $numResults);
		$response = $response->withHeader('X-Count', $numResults);
		$response = $response->withHeader('X-Pages', $limit['pages']);
		return self::formatOk($response, $this->repository->getList($filter, self::getSort($args), $limit));
	}


	public function readIdentified(Request $request, Response $response, ArgumentParser $args): Response
	{

		$this->runEvents($this->beforeRequest, $request, $response, $args);
        $data = [];
		foreach ($this->getReadIds($args) as $id) {
            $ent = $this->getObject((int)$id);
            $this->validateDetail($this->getAccess($request));
		    $data = static::getPaginationOnDetail($args, $this->getData($ent));
            if (array_key_exists('maxCount', $data)){
                $maxCount = $data['maxCount'];
                $response = $response->withHeader('X-MaxCount', $maxCount);
                $response = $response->withHeader('X-Pages', $args['perPage'] ? ceil($maxCount / $args['perPage']) : 1);
                unset($data['maxCount']);
            }
        }
        return self::formatOk($response, $data);
	}


	/**
	 * @param int                      $id
	 * @param IEndpointRepository|null $repository
	 * @param string|null              $objectName
	 * @return mixed
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


	// ============================================== HELPERS

	protected static function identifierGetter(): \Closure
	{
		return function(IdentifiedObject $object) {
			return $object->getId();
		};
	}

    public function getUserPermissions($id)
    {
        $auth_user = $this->orm->getRepository(\App\Entity\Authorization\User::class)->find($id);
        $users_groups = $auth_user->getGroups()->map(function (UserGroupToUser $groupLink) {
            $group = $groupLink->getUserGroupId();
            return ['groupId' => $group->getId(), 'roleId' =>(int) $groupLink->getRoleId()];
        });
        $group_permissions = [];
        foreach ($users_groups->toArray() as $group){
            $group_permissions[$group['groupId']] = $group['roleId'];
        }
        return ["group_wise" => $group_permissions, "platform_wise" => $auth_user->getType(), "user_id" => $id];
    }

    /**
     * @param array $user_permissions
     * @return array additional collection filter,
     * key (is group id of users groups) => value (is prepared for dql filter)
     * @throws InvalidArgumentException if user with non-existing role
     * @throws InvalidAuthenticationException
     */
    public function validateList(array $user_permissions) : array
    {
        switch ($user_permissions['platform_wise']){
            case User::ADMIN:
                return [];
            case User::POWER:
            case User::REGISTERED:
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
     * @throws InvalidAuthenticationException
     */
    public function validateDetail(array $user_permissions) : bool
    {
        switch ($user_permissions['platform_wise']){
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
            case User::GUEST:
                $this->hasAccessToObject($user_permissions['group_wise']);
                return true;
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

}
