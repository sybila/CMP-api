<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_specie")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelSpecie implements IdentifiedObject
{

	use SBase;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="model_id")
	 */
	protected $modelId;

	/**
	 * @var int
	 * @ORM\ManyToOne(targetEntity="ModelCompartment", inversedBy="species")
	 * @ORM\JoinColumn(name="model_compartment_id", referencedColumnName="id")
	 */
	protected $compartmentId;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="initial_expression")
	 */
	protected $initialExpression;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="boundary_condition")
	 */
	protected $boundaryCondition;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="has_only_substance_units")
	 */
	protected $hasOnlySubstanceUnits;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="is_constant")
	 */
	protected $constant;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelReactionItem", mappedBy="specieId")
	 */
	protected $reactionItems;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelRule", mappedBy="specieId")
	 */
	protected $rules;

	/**
	 * Get modelId
	 * @return integer
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * Set modelId
	 * @param integer $modelId
	 * @return ModelSpecie
	 */
	public function setModelId($modelId): ModelSpecie
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get compartmentId
	 * @return integer
	 */
	public function getCompartmentId()
	{
		return $this->compartmentId;
	}

	/**
	 * Set compartmentId
	 * @param ModelCompartment $compartmentId
	 * @return ModelSpecie
	 */
	public function setCompartmentId($compartmentId): ModelSpecie
	{
		$this->compartmentId = $compartmentId;
		return $this;
	}

	/**
	 * Get initialExpression
	 * @return string
	 */
	public function getInitialExpression(): ?string
	{
		return $this->initialExpression;
	}

	/**
	 * Set initialExpression
	 * @param string $initialExpression
	 * @return Model
	 */
	public function setInitialExpression($initialExpression): ModelSpecie
	{
		$this->initialExpression = $initialExpression;
		return $this;
	}

	/**
	 * Get boundaryCondition
	 * @return integer
	 */
	public function getBoundaryCondition(): ?int
	{
		return $this->boundaryCondition;
	}

	/**
	 * Set boundaryCondition
	 * @param integer $boundaryCondition
	 * @return ModelSpecie
	 */
	public function setBoundaryCondition($boundaryCondition): ModelSpecie
	{
		$this->boundaryCondition = $boundaryCondition;
		return $this;
	}

	/**
	 * Get hasOnlySubstanceUnits
	 * @return integer
	 */
	public function getHasOnlySubstanceUnits(): int
	{
		return $this->hasOnlySubstanceUnits;
	}

	/**
	 * Set hasOnlySubstanceUnits
	 * @param integer $hasOnlySubstanceUnits
	 * @return ModelSpecie
	 */
	public function setHasOnlySubstanceUnits($hasOnlySubstanceUnits): ModelSpecie
	{
		$this->hasOnlySubstanceUnits = $hasOnlySubstanceUnits;
		return $this;
	}

	/**
	 * Get isConstant
	 * @return integer
	 */
	public function getConstant(): int
	{
		return $this->constant;
	}

	/**
	 * Set isConstant
	 * @param integer $constant
	 * @return ModelSpecie
	 */
	public function setConstant($constant): ModelSpecie
	{
		$this->constant = $constant;
		return $this;
	}

	/**
	 * @return ModelReactionItem[]|Collection
	 */
	public function getReactionItems(): Collection
	{
		return $this->reactionItems;
	}

	/**
	 * @return ModelRule[]|Collection
	 */
	public function getRules(): Collection
	{
		return $this->rules;
	}

}
