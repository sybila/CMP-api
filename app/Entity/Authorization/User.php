<?php

namespace App\Entity\Authorization;

use App\Entity\Identifier;
use App\Entity\IdentifiedObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User implements UserEntityInterface, IdentifiedObject
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
	 * @ORM\Column
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $surname;

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

	/**
	 * @var int
	 * @ORM\Column
	 */
	private $type;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $email;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $phone;

	/**
	 * @ORM\OneToMany(targetEntity="UserGroupToUser", mappedBy="userId")
	 */
	private $groups;


	public function __construct($username)
	{
		$this->username = $username;
		$this->accessTokens = new ArrayCollection;
	}


	/**
	 * Get id
	 * @return integer
	 */
	public function getIdentifier()
	{
		return $this->getId();
	}


	public function setIdentifier($identifier)
	{
		$this->id = (int) $identifier;
	}


	public function getUsername(): string
	{
		return $this->username;
	}


	public function getName()
	{
		return $this->name;
	}


	public function getSurname()
	{
		return $this->surname;
	}


	public function getType()
	{
		return $this->type;
	}


	public function getEmail()
	{

		return $this->email;
	}


	public function getPhone()
	{

		return $this->phone;
	}


	public function getGroups()
	{
		return $this->groups;
	}


	public function setName(string $name)
	{
		$this->name = $name;
		return $this;
	}


	public function setSurname(string $surname)
	{
		$this->surname = $surname;
		return $this;
	}


	public function setPasswordHash(string $passwordHash)
	{
		$this->passwordHash = $passwordHash;
		return $this;
	}


	public function setType(int $type)
	{
		$this->type = $type;
		return $this;
	}


	public function setEmail(string $email)
	{
		$this->email = $email;
		return $this;
	}


	public function setPhone(string $phone)
	{
		$this->phone = $phone;
		return $this;
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
		if (password_needs_rehash($this->passwordHash, self::PASSWORD_ALGORITHM)) {
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
