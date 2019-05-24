<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_constraint")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelConstraint implements IdentifiedObject
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
	protected $message;

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
	 * @return ModelConstraint
	 */
	public function setModelId($modelId): ModelConstraint
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get message
	 * @return null|string
	 */
	public function getMessage(): ?string
	{
		return $this->message;
	}

	/**
	 * Set message
	 * @param string $message
	 * @return ModelConstraint
	 */
	public function setMessage($message): ModelConstraint
	{
		$this->message = $message;
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
	 * @return ModelConstraint
	 */
	public function setFormula($formula): ModelConstraint
	{
		$this->formula = $formula;
		return $this;
	}

}
