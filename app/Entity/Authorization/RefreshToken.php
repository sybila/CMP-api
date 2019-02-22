<?php

namespace App\Entity\Authorization;

use App\Entity\IdentifiedObject;
use App\Entity\Identifier;
use App\Helpers\DateTimeJson;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="refresh_token")
 */
class RefreshToken implements RefreshTokenEntityInterface, IdentifiedObject
{
	use Identifier;

	/**
	 * @var AccessToken
	 * @ORM\OneToOne(targetEntity="AccessToken", inversedBy="refreshToken")
	 * @ORM\JoinColumn(name="access_token_id", referencedColumnName="id", nullable=false)
	 */
	private $accessToken;

	/**
	 * @var DateTimeJson
	 * @ORM\Column(type="datetime",name="created_at")
	 */
	private $createdAt;

	/**
	 * @var DateTimeJson
	 * @ORM\Column(type="datetime",name="expires_at")
	 */
	private $expiresAt;

	/**
	 * @var DateTimeJson
	 * @ORM\Column(type="datetime",name="revoked_at", nullable=true)
	 */
	private $revokedAt;

	public function __construct()
	{
		$this->createdAt = new DateTimeJson;
	}

	/**
	 * @return int
	 */
	public function getIdentifier()
	{
		return $this->getId();
	}

	/**
	 * @param int $identifier
	 */
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

	/**
	 * @param AccessToken|AccessTokenEntityInterface $accessToken
	 */
	public function setAccessToken(AccessTokenEntityInterface $accessToken)
	{
		$this->accessToken = $accessToken;
	}

	/**
	 * @return AccessToken|AccessTokenEntityInterface
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}

	/**
	 * @return DateTimeJson|\DateTime
	 */
	public function getExpiryDateTime()
	{
		return $this->expiresAt;
	}

	/**
	 * @param DateTimeJson|\DateTime $dateTime
	 */
	public function setExpiryDateTime(\DateTime $dateTime)
	{
		$this->expiresAt = $dateTime;
	}
}
