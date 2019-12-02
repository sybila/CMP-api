<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="event")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentEvent implements IdentifiedObject
{
	use EBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Experiment", inversedBy="events")
	 * @ORM\JoinColumn(name="exp_id", referencedColumnName="id")
	 */
	protected $experimentId;

	/**
	 * @var float
	 * @ORM\Column(type="float", name="time")
	 */
	private $time;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="event")
	 */
	private $event;

    /**
     * @var string
     * @ORM\Column(type="string", columnDefinition="ENUM('error', 'change', 'info')")
     */
    private $type;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="is_automatic")
     */
    private $isAutomatic;
	
	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get experimentId
	 * @return integer
	 */
	public function getExperimentId()
	{
		return $this->experimentId;
	}

	/**
	 * Set experimentId
	 * @param integer $experimentId
	 * @return ExperimentEvent
	 */
	public function setExperimentId($experimentId): ExperimentEvent
	{
		$this->experimentId = $experimentId;
		return $this;
	}


	/**
	 * Get time
	 * @return null|float
	 */
	public function getTime(): ?float
	{
		return $this->time;
	}

	/**
	 * Set time
	 * @param float $time
	 * @return ExperimentEvent
	 */
	public function setTime($time): ExperimentEvent
	{
		$this->time = $time;
		return $this;
	}

	/**
	 * Get event
	 * @return string|null
	 */
	public function getEvent(): ?string
	{
		return $this->event;
	}

	/**
	 * Set event
	 * @param string $event
	 * @return ExperimentEvent
	 */
	public function setEvent($event): ExperimentEvent
	{
		$this->event = $event;
		return $this;
	}

	/**
	 * Get type
	 * @return string|null
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * Set type
	 * @param string $type
	 * @return ExperimentEvent
	 */
	public function setType($type): ExperimentEvent
	{
		$this->type = $type;
		return $this;
	}

    /**
     * Get type
     * @return boolean
     */
    public function getIsAutomatic(): bool
    {
        return $this->isAutomatic;
    }

    /**
     * Set isAutomatic
     * @param boolean $isAutomatic
     * @return ExperimentEvent
     */
    public function setIsAutomatic($isAutomatic): ExperimentEvent
    {
        $this->isAutomatic = $isAutomatic;
        return $this;
    }
}