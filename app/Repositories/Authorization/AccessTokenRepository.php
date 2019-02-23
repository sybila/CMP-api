<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\AccessToken;
use App\Entity\Repositories\IRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Psr\Container\ContainerInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface, IRepository
{
	/** @var EntityManager */
	private $em;

	/** @var ObjectRepository */
	private $accessTokenRepository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->accessTokenRepository = $em->getRepository(AccessToken::class);
	}

	public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
	{
		$token = new AccessToken;
		if ($userIdentifier)
			$token->setUserIdentifier($userIdentifier);

		return $token;
	}

	/**
	 * @param AccessToken|AccessTokenEntityInterface $accessTokenEntity
	 * @inheritDoc
	 */
	public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
	{
		try {
			$this->em->persist($accessTokenEntity);
			$this->em->flush();
		}
		catch (UniqueConstraintViolationException $e) {
			throw UniqueTokenIdentifierConstraintViolationException::create();
		}
	}

	public function revokeAccessToken($tokenId)
	{
		$token = $this->accessTokenRepository->find($tokenId);
		if (!$token)
			return;

		$token->setRevoked();
		$this->em->persist($token);
		$this->em->flush();
	}

	public function isAccessTokenRevoked($tokenId)
	{
		$token = $this->accessTokenRepository->find($tokenId);
		return !$token || $token->isRevoked();
	}
}
