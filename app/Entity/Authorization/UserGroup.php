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


	public function setIdentifier($identifier)
	{
		$this->id = (int) $identifier;
	}

}
