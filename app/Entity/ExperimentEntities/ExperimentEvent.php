<?php

namespace App\Entity;

use Consistence\Type\Type;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\ManyToOne(targetEntity="ExperimentEventType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $typeId;

    /**
     * @ORM\ManyToOne(targetEntity="Device")
     * @ORM\JoinColumn(name="device_id", referencedColumnName="id")
     */
    protected $deviceId;


    /**
     * @var bool
     * @ORM\Column(type="boolean", name="success")
     */
    private $success;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ExperimentEventArg", mappedBy="eventId")
     */
    private $args;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ExperimentEventResponse", mappedBy="eventId")
     */
    private $responses;


	
	/**
	 * Get ExperimentEvent
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
     * Get typeId
     * @return ExperimentEventType
     */
    public function getTypeId(): ?ExperimentEventType
    {
        return $this->typeId;
    }

    /**
     * Set type
     * @param ExperimentEventType $type
     * @return ExperimentEvent
     */
    public function setTypeId($type): ExperimentEvent
    {
        $this->typeId = $type;
        return $this;
    }

    /**
     * Get deviceId
     * @return Device
     */
    public function getDeviceId(): ?Device
    {
        return $this->deviceId;
    }

    /**
     * Set deviceId
     * @param Device $device
     * @return ExperimentEvent
     */
    public function setDeviceId($device): ExperimentEvent
    {
        $this->deviceId = $device;
        return $this;
    }


    /**
     * Get type
     * @return boolean
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Set success
     * @param boolean $success
     * @return ExperimentEvent
     */
    public function setSuccess($success): ExperimentEvent
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return ExperimentEventArg[]|Collection
     */
    public function getArgs(): Collection
    {
        return $this->args;
    }

    /**
     * @return ExperimentEventResponse[]|Collection
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }
}