<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\User;
use App\Entity\Repositories\IEndpointRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface, IEndpointRepository
{

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
