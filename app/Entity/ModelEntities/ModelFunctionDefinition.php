<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_function_definition")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelFunctionDefinition implements IdentifiedObject
{
	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $formula;

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
	 * @return ModelCompartment
	 */
	public function setModelId($modelId): ModelCompartment
	{
		$this->modelId = $modelId;
		return $this;
	}


	/**
	 * Get formula
	 * @return null|string
	 */
	public function getFormula(): ?string
	{
		return $this->formula;
	}

	/**
	 * Set formula
	 * @param string $formula
	 * @return ModelEventAssignment
	 */
	public function setFormula($formula): ModelEventAssignment
	{
		$this->formula = $formula;
		return $this;
	}

}
