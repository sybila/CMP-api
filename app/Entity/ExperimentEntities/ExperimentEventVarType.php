<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="event_var_type")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentEventVarType implements IdentifiedObject
{
	use EBase;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="type")
	 */
	private $type;


	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): int
	{
		return $this->id;
	}
	
	/**
	 * Get type
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set type
	 * @param string type
	 * @return ExperimentEventVarType
	 */
	public function setType($type): ExperimentEventVarType
	{
		$this->type = $type;
		return $this;
	}
}