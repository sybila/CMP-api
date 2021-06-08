<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\User;
use App\Entity\Repositories\IEndpointRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\AttachEntityListenersListener;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface, IEndpointRepository
{

	/** @var EntityManager */
	private $em;

	/** @var EntityRepository  */
	private $userRepository;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->userRepository = $em->getRepository(User::class);
	}


	public function getById(int $id): User
    {
        /** @var User $usr */
        $usr = $this->userRepository->find($id);
		return $usr;
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
        return $this->userRepository
            ->matching($this->createQueryCriteria($filter, $limit, $sort))
            ->map(function (User $user) {
                return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'type' => [
                    'id' => $user->getType()->getId(),
                    'tier' => $user->getType()->getTier(),
                    'name' => $user->getType()->getName()],
                'email' => $user->getEmail()];
            })->toArray();
	}

    /**
     * @param array $filter
     * @param array|null $limit
     * @param array|null $sort
     * @return Criteria
     */
    public function createQueryCriteria(array $filter, array $limit = null, array $sort = null): Criteria
    {
        $criteria = Criteria::create()->where(Criteria::expr()->in('id', $filter['accessFilter']['id']));
        foreach ($filter['argFilter'] as $by => $expr){
            $criteria = $criteria->andWhere(Criteria::expr()->contains($by, $expr));
        }
        return $criteria->setMaxResults($limit['limit'] ? $limit['limit'] : null)
            ->setFirstResult($limit['offset'] ? $limit['offset'] : null)
            ->orderBy($sort ? $sort : []);
    }


	public function getNumResults(array $filter): int
	{
        return $this->userRepository
            ->matching($this->createQueryCriteria($filter))
            ->count();
	}

}
