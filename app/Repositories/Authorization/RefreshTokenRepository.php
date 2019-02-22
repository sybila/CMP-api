<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\RefreshToken;
use App\Entity\Repositories\IRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface, IRepository
{
	/** @var EntityManager */
	private $em;

	/** @var ObjectRepository */
	private $refreshTokenRepository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->refreshTokenRepository = $em->getRepository(RefreshToken::class);
	}

	public function getNewRefreshToken()
	{
		return new RefreshToken;
	}

	public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
	{
		try {
			$this->em->persist($refreshTokenEntity);
			$this->em->flush();
		}
		catch (UniqueConstraintViolationException $e) {
			throw UniqueTokenIdentifierConstraintViolationException::create();
		}
	}

	public function revokeRefreshToken($tokenId)
	{
		$token = $this->refreshTokenRepository->find($tokenId);
		if (!$token)
			return;

		$token->setRevoked();
		$this->em->persist($token);
		$this->em->flush();
	}

	public function isRefreshTokenRevoked($tokenId)
	{
		$token = $this->refreshTokenRepository->find($tokenId);
		return !$token || $token->isRevoked();
	}
}
