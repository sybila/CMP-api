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
 * @ORM\Table(name="user_group")
 */
class UserGroup implements IdentifiedObject
{

    const PUBLIC_SPACE = 1;
    const ADMIN_SPACE = 2;

	use Identifier;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $name;

	/**
	 * @ORM\OneToMany(targetEntity="UserGroupToUser", mappedBy="userGroupId")
	 */
	private $users;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $description;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $type;

    /**
     * @var boolean
     * @ORM\Column(name="is_public")
     */
	private $isPublic;

	public function getIdentifier()
	{
		return $this->getId();
	}


	public function getName()
	{
		return $this->name;
	}


	public function getUsers()
	{
		return $this->users;
	}


	public function getType()
	{
		return $this->type;
	}


	public function getDescription()
	{
		return $this->description;
	}


	public function setIdentifier($identifier)
	{
		$this->id = (int) $identifier;
	}


	public function setName(string $name)
	{
		$this->name = $name;
		return $name;
	}


	public function setType(int $type)
	{
		$this->type = $type;
		return $type;
	}


	public function setDescription(string $description)
	{
		$this->description = $description;
		return $description;
	}


    public function getIsPublic()
    {
        return $this->isPublic;
    }


    public function setIsPublic($isPublic): void
    {
        $this->isPublic = $isPublic;
    }


}
