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

    const USER_SPACE = 3;
    const WORK_SPACE = 4;

    const SPACES = [
        'user space' => self::USER_SPACE,
        'work space' => self::WORK_SPACE
    ];

	use Identifier;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $name;

	/**
	 * @ORM\OneToMany(targetEntity="UserGroupToUser", mappedBy="userGroupId", cascade={"remove"})
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
	private $type = self::USER_SPACE;

    /**
     * @var bool
     * @ORM\Column(name="is_public")
     */
	private $isPublic = false;

	public function getIdentifier()
	{
		return $this->getId();
	}


	public function getName()
	{
		return $this->name;
	}


    //FIXME: refactor, this returns only links
	public function getUsers(): Collection
	{
		return $this->users;
	}

    public function getAllUsers(): Collection
    {
        return $this->getUsers()->map(function (UserGroupToUser $userGroupToUser) {
            return $userGroupToUser->getUserId();
        });
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
	}


	public function setType(string $type)
	{
		$this->type = $type;

	}


	public function setDescription(string $description)
	{
		$this->description = $description;
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
