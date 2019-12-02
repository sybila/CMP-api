<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="unit")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Unit implements IdentifiedObject
{
	use EBase;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="name")
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="code")
	 */
	private $code;

	/**
	 * Get name
	 * @return string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Set name
	 * @param string $name
	 * @return Unit
	 */
	public function setName($name): Unit
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get code
	 * @return string
	 */
	public function getCode(): ?string
	{
		return $this->code;
	}

	/**
	 * Set code
	 * @param string $code
	 * @return Unit
	 */
	public function setCode($code): Unit
	{
		$this->code = $code;
		return $this;
	}
}
