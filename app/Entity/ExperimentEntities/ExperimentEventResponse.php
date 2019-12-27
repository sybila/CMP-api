<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="event_response")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentEventResponse implements IdentifiedObject
{
	use EBase;

	/**
	 * @ORM\ManyToOne(targetEntity="ExperimentEvent", inversedBy="responses")
	 * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
	 */
	protected $eventId;

	/**
	 * @ORM\ManyToOne(targetEntity="ExperimentEventVarType")
	 * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
	 */
	protected $typeId;


	/**
	 * @var string
	 * @ORM\Column(type="string", name="value")
	 */
	private $value;

	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): int
	{
		return $this->id;
	}
	
	/**
	 * Get eventId
	 * @return integer
	 */
	public function getEventId()
	{
		return $this->eventId;
	}

	/**
	 * Set eventId
	 * @param integer $eventId
	 * @return ExperimentEventResponse
	 */
	public function setEventId($eventId): ExperimentEventResponse
	{
		$this->eventId = $eventId;
		return $this;
	}

    /**
     * Get typeId
     * @return ExperimentEventVarType
     */
    public function getTypeId() : ExperimentEventVarType
    {
        return $this->typeId;
    }

    /**
     * Set typeId
     * @param ExperimentEventVarType $typeId
     * @return ExperimentEventResponse
     */
    public function setTypeId($typeId): ExperimentEventResponse
    {
        $this->typeId = $typeId;
        return $this;
    }


	/**
	 * Get value
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * Set value
	 * @param string $value
	 * @return ExperimentEventResponse
	 */
	public function setValue($value): ExperimentEventResponse
	{
		$this->value = $value;
		return $this;
	}
}