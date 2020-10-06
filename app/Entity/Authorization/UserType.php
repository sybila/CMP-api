<?php

namespace App\Entity\Authorization;

use App\Entity\Identifier;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_type")
 */
class UserType implements IdentifiedObject
{

	use Identifier;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
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
