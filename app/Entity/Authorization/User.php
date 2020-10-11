<?php

namespace App\Entity\Authorization;

use App\Entity\Identifier;
use App\Entity\IdentifiedObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User implements UserEntityInterface, IdentifiedObject
{

	use Identifier;

    /**
     * constants for platform user types
     */
    const ADMIN = 1;
    const POWER = 2;
    const REGISTERED = 3;
    const TEMPORARY = 4;
    const GUEST = 0; //pun intended

    const CAN_ADD = [4,5,6,7,8];
    const CAN_EDIT = [2,3,6,7,8];
    const CAN_DELETE = [1,3,5,7,8];
    const OWNER_ROLE = 8;

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
     * @ORM\OneToOne(targetEntity="UserType", mappedBy="tier")
     * @ORM\JoinColumn(name="type")
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
     * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="UserGroupToUser", mappedBy="userId")
	 */
	private $groups;

    /**
     * @var boolean
     * @ORM\Column(name="is_public")
     */
	private $isPublic;


	public function __construct($username)
	{
		$this->username = $username;
		$this->accessTokens = new ArrayCollection;
		$this->groups = new ArrayCollection();
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

    /**
     * @return Collection|UserGroupToUser[]
     */
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


	public function setType(UserType $type)
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


    public function getIsPublic()
    {
        return $this->isPublic;
    }


    public function setIsPublic($isPublic): void
    {
        $this->isPublic = $isPublic;
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
