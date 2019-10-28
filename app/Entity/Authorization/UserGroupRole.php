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
 * @ORM\Table(name="user_group_role")
 */
class UserGroupRole implements IdentifiedObject
{

	use Identifier;

	/**
	 * @var int
	 * @ORM\Column
	 */
	private $tier;

	/**
	 * @var string
	 * @ORM\Column
	 */
	private $name;


	public function getTier()
	{
		return $this->tier;
	}


	public function getName()
	{
		return $this->name;
	}


	public function setTier(int $tier)
	{
		$this->tier = $tier;
		return $this;
	}


	public function setName(string $name)
	{

		$this->name = $name;
		return $this;
	}

}
