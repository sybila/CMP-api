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

    //* @ORM\Column(type="string")

    /**
	 * @ORM\OneToOne(targetEntity="MathExpression", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="formula", referencedColumnName="id")
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

    public function getFormula()
    {
        return $this->formula;
    }

    public function setFormula(MathExpression $formula): void
    {
        $this->formula = $formula;
    }
//
//	/**
//	 * Get formula
//	 * @return null|string
//	 */
//	public function getFormula(): ?string
//	{
//		return $this->formula;
//	}
//
//	/**
//	 * Set formula
//	 * @param string $formula
//	 * @return ModelEventAssignment
//	 */
//	public function setFormula($formula): ModelEventAssignment
//	{
//		$this->formula = $formula;
//		return $this;
//	}



}
