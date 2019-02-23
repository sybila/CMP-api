<?php

namespace App\Entity\Authorization;

use App\Entity\IdentifiedObject;
use App\Entity\Identifier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ClientEntityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="client")
 */
class Client implements ClientEntityInterface, IdentifiedObject
{
	use Identifier;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(name="redirect_uri")
	 */
	private $redirectUri;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $secret;

	/**
	 * @var AccessToken[]|Collection
	 * @ORM\OneToMany(targetEntity="AccessToken", mappedBy="client")
	 */
	private $accessTokens;

	public function __construct()
	{
		$this->accessTokens = new ArrayCollection;
	}

	public function getIdentifier()
	{
		return $this->getId();
	}

	public function setIdentifier($identifier)
	{
		$this->id = (int)$identifier;
	}

	public function getSecret(): string
	{
		return $this->secret;
	}

	public function setSecret(string $secret): void
	{
		$this->secret = $secret;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getRedirectUri(): string
	{
		return $this->redirectUri;
	}

	public function setRedirectUri(string $redirectUri): void
	{
		$this->redirectUri = $redirectUri;
	}

	/**
	 * @return AccessToken[]|Collection
	 */
	public function getAccessTokens(): Collection
	{
		return $this->accessTokens;
	}

	public function addAccessToken(AccessToken $token): void
	{
		$this->accessTokens->add($token);
	}

	public function removeAccessToken(AccessToken $token): void
	{
		$this->accessTokens->removeElement($token);
	}
}
