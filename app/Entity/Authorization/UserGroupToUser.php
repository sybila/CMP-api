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
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="id")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
	private $userId;

    /**
     * @var integer
     * @ORM\Column(name="user_id")
     */
    private $u_id;
    //FIXME this is the same var as userId, having just another functionaly bcs of ORM, needed?
    //FIXME careful, this plays role in UserRepository and RepoAccessController

    /**
     * @var integer
     * @ORM\Column(name="user_group_id")
     */
	private $groupId;
	//FIXME this is the same var as userGroupId, having just another functionaly bcs of ORM, needed?
    //FIXME careful, this plays role in UserRepository and RepoAccessController

	/**
	 * @ORM\ManyToOne(targetEntity="UserGroup", inversedBy="userId")
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
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }




}
