<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\User;
use App\Entity\Repositories\IRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface, IRepository
{
	/** @var EntityManager */
	private $em;

	/** @var ObjectRepository */
	private $userRepository;

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

		if ($user && $user->checkPassword($password))
		{
			if ($user->rehashPassword($password))
			{
				$this->em->persist($user);
				$this->em->flush();
			}

			return $user;
		}

		return null;
	}
}
