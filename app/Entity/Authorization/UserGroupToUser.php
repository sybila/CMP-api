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
 * @ORM\Table(name="user_group_to_user")
 */
class UserGroupToUser implements IdentifiedObject
{

	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="groups")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
	private $userId;

	/**
	 * @ORM\ManyToOne(targetEntity="UserGroup", inversedBy="users")
	 * @ORM\JoinColumn(name="user_group_id", referencedColumnName="id")
     */
	private $userGroupId;

	/**
	 * @var integer
	 * @ORM\Column(name="user_group_role_id")
	 */
	private $roleId;


	public function getId(): ?int
	{
		return $this->id;
	}


	public function getUserId()
	{
		return $this->userId;
	}


	public function getUserGroupId()
	{
		return $this->userGroupId;
	}


	public function getRoleId()
	{
		return $this->roleId;
	}


    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @param mixed $userGroupId
     */
    public function setUserGroupId($userGroupId): void
    {
        $this->userGroupId = $userGroupId;
    }

    /**
     * @param int $roleId
     */
    public function setRoleId(int $roleId): void
    {
        $this->roleId = $roleId;
    }




}
