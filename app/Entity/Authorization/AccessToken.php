<?php

namespace App\Entity\Authorization;

use App\Repositories\Authorization\ScopeRepository;
use App\Entity\IdentifiedObject;
use App\Entity\Identifier;
use App\Helpers\DateTimeJson;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="access_token")
 * @ORM\HasLifecycleCallbacks
 */
class AccessToken implements AccessTokenEntityInterface, IdentifiedObject
{
	use Identifier;
	use AccessTokenTrait;

	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="User",inversedBy="accessTokens")
	 * @ORM\JoinColumn(name="user_id")
	 */
	private $user;

	/** @var int|null */
	private $userId = null;

	/**
	 * @var Client
	 * @ORM\ManyToOne(targetEntity="Client",inversedBy="accessTokens")
	 * @ORM\JoinColumn(name="client_id")
	 */
	private $client;

	/**
	 * @var string
	 * @ORM\Column(name="scopes",type="string")
	 */
	private $scopesJson;

	/**
	 * @var Scope[]|array
	 */
	private $scopes = [];

	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime", name="expires_at")
	 */
	private $expiresAt;

	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime", name="revoked_at", nullable=true)
	 */
	private $revokedAt;

	/**
	 * @var RefreshToken|null
	 * @ORM\OneToOne(targetEntity="RefreshToken", mappedBy="accessToken")
	 */
	private $refreshToken;

	public function getIdentifier()
	{
		return $this->getId();
	}

	public function setIdentifier($identifier)
	{
		$this->id = (int)$identifier;
	}

	public function isRevoked(): bool
	{
		return $this->revokedAt !== null;
	}

	public function setRevoked(): void
	{
		$this->revokedAt = new DateTimeJson;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(User $user): void
	{
		$this->user = $user;
	}

	public function getClient(): Client
	{
		return $this->client;
	}

	/**
	 * @param Client|ClientEntityInterface $client
	 */
	public function setClient(ClientEntityInterface $client)
	{
		$this->client = $client;
	}

	/**
	 * @return ScopeEntityInterface[]|Collection
	 */
	public function getScopes()
	{
		return array_values($this->scopes);
	}

	public function getRefreshToken(): ?RefreshToken
	{
		return $this->refreshToken;
	}

	// ============================== AccessTokenEntityInterface methods

	public function getExpiryDateTime(): \DateTimeImmutable
	{
		return $this->expiresAt;
	}

	public function setExpiresAt(\DateTimeImmutable $expiresAt): void
	{
		$this->expiresAt = $expiresAt;
	}

	/**
	 * @return int
	 */
	public function getUserIdentifier()
	{
		return $this->getUser() ? $this->getUser()->getIdentifier() : 0;
	}

	public function setExpiryDateTime(\DateTime $dateTime)
	{
		$this->expiresAt = DateTimeJson::createFromDateTime($dateTime);
	}

	public function setUserIdentifier($identifier)
	{
		$this->userId = $identifier;
	}

	public function addScope(ScopeEntityInterface $scope)
	{
		$this->scopes[$scope->getIdentifier()] = $scope;
	}

	// ============================== EVENTS

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 * @param LifecycleEventArgs $args
	 */
	public function onSave(LifecycleEventArgs $args)
	{
		$this->scopesJson = json_encode(array_keys($this->scopes));

		if ($this->userId !== null)
		{
			$this->user = $args->getEntityManager()->getRepository(User::class)->find($this->userId);
			$this->userId = null;
		}
	}

	/**
	 * @ORM\PostLoad
	 */
	public function onLoad()
	{
		$scopeIdentifiers = json_decode($this->scopesJson);
		if (!is_array($scopeIdentifiers))
			return;

		$scopes = [];
		foreach ($scopeIdentifiers as $id)
		{
			$scope = ScopeRepository::getById((string)$id);
			if ($scope)
				$scopes[$scope->getIdentifier()] = $scope;
		}

		$this->scopes = $scopes;
	}
}
