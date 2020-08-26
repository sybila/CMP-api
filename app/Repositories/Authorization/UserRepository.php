<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\User;
use App\Entity\Authorization\UserGroup;
use App\Entity\Authorization\UserGroupToUser;
use App\Entity\Experiment;
use App\Entity\Model;
use App\Entity\Repositories\IEndpointRepository;
use App\Exceptions\InvalidAuthenticationException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\AttachEntityListenersListener;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface, IEndpointRepository
{
    /**
     * constants for platform user types
     */
    const ADMIN = 1;
    const POWER = 2;
    const REGISTERED = 3;
    const GUEST = 4;

    const CAN_ADD = [4,5,6,7,8];
    const CAN_EDIT = [2,3,6,7,8];
    const CAN_DELETE = [1,3,5,7,8];


	/** @var EntityManager */
	private $em;

	/** @var ObjectRepository */
	private $userRepository;

	/** @var User */
	private $user;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->userRepository = $em->getRepository(User::class);
	}


	public function getById(int $id): ?User
	{
		return $this->userRepository->find($id);
	}


	public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $client)
	{
		$user = $this->userRepository->findOneBy(['username' => $username]);

		if ($user && $user->checkPassword($password)) {
			if ($user->rehashPassword($password)) {
				$this->em->persist($user);
				$this->em->flush();
			}

			return $user;
		}

		return null;
	}


	public function get(int $id)
	{
		return $this->em->find(User::class, $id);
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('u.id, u.username, u.type, u.name, u.surname, u.email, u.phone');
		return $query->getQuery()->getArrayResult();
	}

	public function getGroups(string $u_id){
	    $query = $this->em->createQueryBuilder()
            ->from(UserGroupToUser::class, 'g');
        $query->select('g.groupId, g.roleId')
            ->where("g.userId = {$u_id}");
        return $query->getQuery()->getArrayResult();
    }


    public function getVisibleUsersId(array $groups){
        $query = $this->em->createQueryBuilder()
            ->from(UserGroupToUser::class,'u')
            ->innerJoin(User::class, 'us', 'WITH', 'us.id = u.userId')
            ->select('us.id')
            ->where('us.isPublic = true');
        foreach ($groups as $id => $role){
            if($id != 1) //is not in Public space group
            {
                $query = $query->orWhere("u.groupId = $id");
            }
        }
        return $query->distinct()->getQuery()->getArrayResult();
    }

    public function getVisibleGroups(array $groups){
        $query = $this->em->createQueryBuilder()
            ->from(UserGroup::class,'u')
            ->select('u.groupId')
            ->where('u.isPublic = true');
        return $query->getQuery()->getArrayResult();
	}


        public function hasAccessToObject(string $parent, ?int $id, array $userGroups): ?int
    {
        $parentClass = null;
        switch ($parent) {
            case 'models':
                $parentClass = Model::class;
                break;
            case 'experiments':
                $parentClass = Experiment::class;
                break;
            case 'userGroups':
            case 'users':
                return null;
        }
        if($id) {
            $query = $this->em->createQueryBuilder()
                ->from($parentClass,'o')
                ->select('o.groupId')
                ->where("o.id = $id");
            $groupId = $query->getQuery()->getArrayResult()[0]['groupId'];
            if(array_key_exists($groupId, $userGroups))
            {
                return $groupId;
            } else {
                throw new InvalidAuthenticationException("You cannot access this resource.");
            }
        }
        return null;
    }

    public function confirmAccessibleUserModuleClasses(string $class, int $c_id, array $groups) {
	    //TODO: need to return array of (IDs) of user or userGroups that are visible for this user
        $query = $this->em->createQueryBuilder();
        switch ($class){
            case 'userGroups':
                $query->from(UserGroup::class,'u')
                    ->select('u.groupId');
                break;
            case 'users':
                $query->from(UserGroupToUser::class,'u')
                    ->select('u.userId');
                foreach ($groups as $id => $role){
                    $query = $query->orWhere("u.groupId = $id");
                }
                break;
        }
        $query = $query->orWhere('u.isPublic = true')->distinct();
        $accesible = $query->getQuery()->getArrayResult();
    }

    public function getUserObjects(string $parent, array $groups): array
    {
        $parentClass = null;
	    switch ($parent){
            case 'models':
                $parentClass = Model::class;
                break;
            case 'experiments':
                $parentClass = Experiment::class;
                break;
            case 'users':
                return $groups;
        }
	    $query = $this->em->createQueryBuilder()
            ->from($parentClass,'o')
            ->select('o.id, o.groupId');
	    foreach ($groups as $id => $dql_trash){
	        $query = $query -> orWhere("o.groupId = $id");
        }
	    return $query->getQuery()->getArrayResult();
    }


	public function getNumResults(array $filter): int
	{
		return ((int) $this->buildListQuery($filter)
				->select('COUNT(u)')
				->getQuery()
				->getScalarResult());
	}


	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(User::class, 'u');
		return $query;
	}

}
