<?php

namespace App\Entity\Authorization;

use App\Entity\Identifier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="ep_user")
 */
class User implements UserEntityInterface
{
	use Identifier;

	const PASSWORD_ALGORITHM = PASSWORD_DEFAULT;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $username;

	/**
	 * @var string
	 * @ORM\Column(name="password_hash")
	 */
	private $passwordHash;

	/**
	 * @var AccessToken[]|Collection
	 * @ORM\OneToMany(targetEntity="AccessToken", mappedBy="client")
	 */
	private $accessTokens;

	public function __construct($username)
	{
		$this->username = $username;
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

	public function getUsername(): string
	{
		return $this->username;
	}

	public function changePassword($old, $new): bool
	{
		if (!$this->checkPassword($old))
			return false;

		$this->passwordHash = self::hashPassword($new);
		return true;
	}

	public function rehashPassword(string $password): bool
	{
		if (password_needs_rehash($this->passwordHash, self::PASSWORD_ALGORITHM))
		{
			$this->passwordHash = self::hashPassword($password);
			return true;
		}

		return false;
	}

	public function checkPassword(string $password): bool
	{
		return password_verify($password, $this->passwordHash);
	}

	public static function hashPassword(string $password): string
	{
		return password_hash($password, self::PASSWORD_ALGORITHM);
	}
}
