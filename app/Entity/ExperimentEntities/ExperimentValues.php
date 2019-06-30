<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_values")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentValues implements IdentifiedObject
{
	use EBase;

	/**
	 * @ORM\ManyToOne(targetEntity="ExperimentVariable", inversedBy="variable")
	 * @ORM\JoinColumn(name="experimentVariableId", referencedColumnName="id")
	 */
	protected $variableId;

		/**
	 * @var float
	 * @ORM\Column(type="float", name="time")
	 */
	private $time;

	/**
	 * @var float
	 * @ORM\Column(type="float", name="value")
	 */
	private $value;
	
	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get variableId
	 * @return integer|null
	 */
	public function getVariableId()
	{
		return $this->variableId;
	}

	/**
	 * Set variableId
	 * @param integer $variableId
	 * @return ExperimentValue
	 */
	public function setVariableId($variableId): ExperimentValue
	{
		$this->variableId = $variableId;
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
	 * @return ExperimentValue
	 */
	public function setTime($time): ExperimentValue
	{
		$this->time = $time;
		return $this;
	}

	/**
	 * Get value
	 * @return null|float
	 */
	public function getValue(): ?float
	{
		return $this->value;
	}

	/**
	 * Set value
	 * @param float $value
	 * @return ExperimentValue
	 */
	public function setValue($value): ExperimentValue
	{
		$this->value = $value;
		return $this;
	}
}
	
