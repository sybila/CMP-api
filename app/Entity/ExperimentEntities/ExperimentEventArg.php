<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="event_arg")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentEventArg implements IdentifiedObject
{
	use EBase;

	/**
	 * @ORM\ManyToOne(targetEntity="ExperimentEvent", inversedBy="args")
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
	 * @return ExperimentEventArg
	 */
	public function setEventId($eventId): ExperimentEventArg
	{
		$this->eventId = $eventId;
		return $this;
	}

    /**
     * Get typeId
     * @return ExperimentEventVarType
     */
    public function getTypeId() :ExperimentEventVarType
    {
        return $this->typeId;
    }

    /**
     * Set typeId
     * @param ExperimentEventVarType $typeId
     * @return ExperimentEventArg
     */
    public function setTypeId($typeId): ExperimentEventArg
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
	 * @return ExperimentEventArg
	 */
	public function setValue($value): ExperimentEventArg
	{
		$this->value = $value;
		return $this;
	}
}