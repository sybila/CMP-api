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
     * @ORM\OneToOne(targetEntity="MathExpression", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="formula", referencedColumnName="id")
     */
	protected $expression;

	/**
	 * Get modelId
	 * @return integer|null
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

    /**
     * @param $modelId
     */
	public function setModelId($modelId)
	{
		$this->modelId = $modelId;
	}


	public function getExpression()
	{
		return $this->expression;
	}


	public function setExpression($expression)
	{
		$this->expression = $expression;
	}

}
