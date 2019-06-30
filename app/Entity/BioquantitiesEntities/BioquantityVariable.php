<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="bioquantity_method_var")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class BioquantityVariable implements IdentifiedObject
{
	use EBase;

	/**
	 * @ORM\ManyToOne(targetEntity="BioquantityMethod", inversedBy="variables")
	 * @ORM\JoinColumn(name="id_bioquantity_form", referencedColumnName="id")
	 */
	protected $methodId;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="name_var")
	 */
	private $name;
	

	/**
	 * @var float
	 * @ORM\Column(type="float", name="value")
	 */
	private $value;

	/**
	 * @var float
	 * @ORM\Column(type="float", name="time")
	 */
	private $time;

	/**
	 * @ORM\ManyToOne(targetEntity="ExperimentVariable", inversedBy="bioquantityVariables")
	 * @ORM\JoinColumn(name="id_var_experiment", referencedColumnName="id")
	 */
	protected $experimentVariableId;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="source")
	 */
	private $source;

	
	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get methodId
	 * @return integer|null
	 */
	public function getMethodId()
	{
		return $this->methodId;
	}

	/**
	 * Set methodId
	 * @param integer $variableId
	 * @return BioquantityVariable
	 */
	public function setMethodId($methodId): BioquantityVariable
	{
		$this->methodId = $methodId;
		return $this;
	}

	/**
	 * Get name
	 * @return string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Set name
	 * @param string $name
	 * @return BioquantityVariable
	 */
	public function setName($name): BioquantityVariable
	{
		$this->name = $name;
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

	/**
	 * Get experimentVariableId
	 * @return null|integer
	 */
	public function getExperimentVariableId(): ?integer
	{
		return $this->experimentVariableId;
	}

	/**
	 * Set experimentVariableId
	 * @param integer $experimentVariableId
	 * @return BioquantityVariable
	 */
	public function setExperimentVariableId($experimentVariableId): BioquantityVariable
	{
		$this->experimentVariableId = $experimentVariableId;
		return $this;
	}

	/**
	 * Get source
	 * @return null|string
	 */
	public function getSource(): ?string
	{
		return $this->source;
	}

	/**
	 * Set source
	 * @param string $source
	 * @return BioquantityVariable
	 */
	public function setSource($source): BioquantityVariable
	{
		$this->source = $source;
		return $this;
	}
}
	
