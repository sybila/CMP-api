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
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelEventAssignment", mappedBy="eventId")
	 */
	protected $eventAssignments;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="event_trigger")
	 */
	protected $trigger;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $delay;
	/**
	 * @var string
	 * @ORM\Column(type="string")
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

	/**
	 * Get trigger
	 * @return null|string
	 */
	public function getTrigger(): ?string
	{
		return $this->trigger;
	}

	/**
	 * Set trigger
	 * @param string $trigger
	 * @return ModelEvent
	 */
	public function setTrigger($trigger): ModelEvent
	{
		$this->trigger = $trigger;
		return $this;
	}

	/**
	 * Get delay
	 * @return null|string
	 */
	public function getDelay(): ?string
	{
		return $this->delay;
	}

	/**
	 * Set delay
	 * @param string $delay
	 * @return ModelEvent
	 */
	public function setDelay($delay): ModelEvent
	{
		$this->delay = $delay;
		return $this;
	}

	/**
	 * Get priority
	 * @return null|string
	 */
	public function getPriority(): ?string
	{
		return $this->priority;
	}

	/**
	 * Set priority
	 * @param string $priority
	 * @return ModelEvent
	 */
	public function setPriority($priority): ModelEvent
	{
		$this->priority = $priority;
		return $this;
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
