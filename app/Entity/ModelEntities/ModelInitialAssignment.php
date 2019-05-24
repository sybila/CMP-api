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
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $formula;

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

	/**
	 * Get formula
	 * @return null|string
	 */
	public function getFormula(): ?string
	{
		return $this->formula;
	}

	/**
	 * Set function
	 * @param string $formula
	 * @return ModelInitialAssignment
	 */
	public function setFormula($formula): ModelInitialAssignment
	{
		$this->formula = $formula;
		return $this;
	}

}
