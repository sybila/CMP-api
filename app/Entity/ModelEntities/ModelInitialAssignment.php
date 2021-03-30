<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_initial_assignment")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelInitialAssignment implements IdentifiedObject
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
	 * @return ModelInitialAssignment
	 */
	public function setModelId($modelId): ModelInitialAssignment
	{
		$this->modelId = $modelId;
		return $this;
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
