<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_event_assignment")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelEventAssignment implements IdentifiedObject
{
	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="ModelEvent", inversedBy="eventAssignments")
	 * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
	 */
	protected $eventId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $formula;

	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get eventId
	 * @return integer|null
	 */
	public function getEventId()
	{
		return $this->eventId;
	}

	/**
	 * Set eventId
	 * @param integer $eventId
	 * @return ModelEventAssignment
	 */
	public function setEventId($eventId): ModelEventAssignment
	{
		$this->eventId = $eventId;
		return $this;
	}

	/**
	 * Get formula
	 * @return null|string
	 */
	public function getFormula(): ?string
	{
		return $this->formula;
	}

	/**
	 * Set formula
	 * @param string $formula
	 * @return ModelEventAssignment
	 */
	public function setFormula($formula): ModelEventAssignment
	{
		$this->formula = $formula;
		return $this;
	}

}
