<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_event")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelEvent implements IdentifiedObject
{
	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

	/**
	 * @ORM\OneToMany(targetEntity="ModelEventAssignment", mappedBy="eventId", cascade={"persist", "remove"})
	 */
	protected $eventAssignments;


	/**
     * @ORM\OneToOne(targetEntity="MathExpression", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="event_trigger", referencedColumnName="id")
     */
	protected $trigger;

    /**
     * @ORM\OneToOne(targetEntity="MathExpression", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="delay", referencedColumnName="id")
     */
	protected $delay;

    /**
     * @ORM\OneToOne(targetEntity="MathExpression", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="priority", referencedColumnName="id")
     */
	protected $priority;

	/**
	 * @var int
	 * @ORM\Column(name="evaluate_on_trigger",type="integer")
	 */
	protected $evaluateOnTrigger;

	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get modelId
	 * @return integer|null
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * Set modelId
	 * @param integer $modelId
	 * @return ModelEvent
	 */
	public function setModelId($modelId): ModelEvent
	{
		$this->modelId = $modelId;
		return $this;
	}


	public function getTrigger()
	{
		return $this->trigger;
	}


	public function setTrigger($trigger)
	{
		$this->trigger = $trigger;
	}


	public function getDelay()
	{
		return $this->delay;
	}


	public function setDelay($delay)
	{
		$this->delay = $delay;
	}


	public function getPriority()
	{
		return $this->priority;
	}

	public function setPriority($priority)
	{
		$this->priority = $priority;
	}

	/**
	 * Get evaluateOnTrigger
	 * @return integer|null
	 */
	public function getEvaluateOnTrigger()
	{
		return $this->evaluateOnTrigger;
	}

	/**
	 * Set evaluateOnTrigger
	 * @param integer $evaluateOnTrigger
	 * @return ModelEvent
	 */
	public function setEvaluateOnTrigger($evaluateOnTrigger): ModelEvent
	{
		$this->evaluateOnTrigger = $evaluateOnTrigger;
		return $this;
	}

	/**
	 * @return ModelEventAssignment[]|Collection
	 */
	public function getEventAssignments(): Collection
	{
		return $this->eventAssignments;
	}

}
