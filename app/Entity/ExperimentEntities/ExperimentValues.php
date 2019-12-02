<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


interface IExpValueObject
{
    public function addValue(ExperimentValues $value);
    public function removeValue(ExperimentValues $value);

    /**
     * @return ExperimentValues[]|Collection
     */
    public function getValues(): Collection;
}

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_variable_value")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentValues implements IdentifiedObject
{
	use EBase;

	/**
	 * @ORM\ManyToOne(targetEntity="ExperimentVariable", inversedBy="values")
	 * @ORM\JoinColumn(name="var_id", referencedColumnName="id")
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
     * @var boolean
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
     * @return ExperimentValues
     */
    public function setExperimentId($experimentId): ExperimentValues
    {
        $this->experimentId = $experimentId;
        return $this;
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
	 * @return ExperimentValues
	 */
	public function setVariableId($variableId): ExperimentValues
	{
		$this->variableId = $variableId;
		return $this;
	}

	/**
	 * Get time
	 * @return float
	 */
	public function getTime(): float
	{
		return $this->time;
	}

	/**
	 * Set time
	 * @param float $time
	 * @return ExperimentValues
	 */
	public function setTime($time): ExperimentValues
	{
		$this->time = $time;
		return $this;
	}

	/**
	 * Get value
	 * @return float
	 */
	public function getValue(): float
	{
		return $this->value;
	}

	/**
	 * Set value
	 * @param float $value
	 * @return ExperimentValues
	 */
	public function setValue($value): ExperimentValues
	{
		$this->value = $value;
		return $this;
	}

    /**
     * Get isAutomatic
     * @return boolean
     */
    public function getIsAutomatic(): bool
    {
        return $this->isAutomatic;
    }

    /**
     * Set isAutomatic
     * @param boolean $isAutomatic
     * @return ExperimentValues
     */
    public function setIsAutomatic($isAutomatic): ExperimentValues
    {
        $this->isAutomatic = $isAutomatic;
        return $this;
    }
}

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_variable")
 */
class VariableValue extends ExperimentValues
{
    use Identifier;

    /**
     * @ORM\ManyToOne(targetEntity="ExperimentVariable", inversedBy="values")
     * @ORM\JoinColumn(name="Id", referencedColumnName="id")
     */
    protected $variable;

    public function setVariable(ExperimentVariable $variable)
    {
        $this->variable = $variable;
    }
}