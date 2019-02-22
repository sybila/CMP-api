<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\Client;
use App\Entity\Repositories\IRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface, IRepository
{
	/** @var ObjectRepository */
	private $clientRepository;

	public function __construct(EntityManager $em)
	{
		$this->clientRepository = $em->getRepository(Client::class);
	}

	public function getById(int $id): ?Client
	{
		return $this->clientRepository->find($id);
	}

	public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null, $mustValidateSecret = true)
	{
		$client = $this->clientRepository->findOneBy(['name' => $clientIdentifier]);
		if (!$client || ($mustValidateSecret && $clientSecret && $clientSecret != $client->getSecret()))
		    return null;

		return $client;
	}
}
