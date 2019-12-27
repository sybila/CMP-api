<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="event_type")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentEventType implements IdentifiedObject
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
	 * @return ExperimentEventType
	 */
	public function setType($type): ExperimentEventType
	{
		$this->type = $type;
		return $this;
	}
}