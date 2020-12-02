<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_compartment")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelCompartment implements IdentifiedObject
{
	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

	/**
	 * @var float
	 * @ORM\Column(name="spatial_dimensions",type="float",nullable=true)
	 */
	protected $spatialDimensions;

	/**
	 * @var float
	 * @ORM\Column(name="size",type="float",nullable=true)
	 */
	protected $size;

	/**
	 * @var boolean
	 * @ORM\Column(name="is_constant",type="integer")
	 */
	protected $isConstant;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelSpecie", mappedBy="compartmentId")
	 */
	protected $species;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelReaction", mappedBy="compartmentId")
	 */
	protected $reactions;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelRule", mappedBy="compartmentId")
	 */
	protected $rules;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelUnitDefinition", mappedBy="compartmentId")
	 */
	protected $unitDefinitions;

	/**
     * This returns model object, non-intuitively
	 * Get model
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
	 * Get spatialDimensions
	 * @return float|null
	 */
	public function getSpatialDimensions(): ?float
	{
		return $this->spatialDimensions;
	}

	/**
	 * Set spatialDimensions
	 * @param integer $spatialDimensions
	 * @return ModelCompartment
	 */
	public function setSpatialDimensions($spatialDimensions): ModelCompartment
	{
		$this->spatialDimensions = $spatialDimensions;
		return $this;
	}

	/**
	 * Get size
	 * @return float|null
	 */
	public function getSize(): ?float
	{
		return $this->size;
	}

	/**
	 * Set size
	 * @param integer $size
	 * @return ModelCompartment
	 */
	public function setSize($size): ModelCompartment
	{
		$this->size = $size;
		return $this;
	}

	/**
	 * Get isConstant
	 * @return integer
	 */
	public function getIsConstant(): int
	{
		return $this->isConstant;
	}

	/**
	 * Set isConstant
	 * @param integer $isConstant
	 * @return ModelCompartment
	 */
	public function setIsConstant($isConstant): ModelCompartment
	{
		$this->isConstant = $isConstant;
		return $this;
	}

	/**
	 * @return ModelSpecie[]|Collection
	 */
	public function getSpecies(): Collection
	{
		return $this->species;
	}

	/**
	 * @return ModelReaction[]|Collection
	 */
	public function getReactions(): Collection
	{
		return $this->reactions;
	}

	/**
	 * @return ModelRule[]|Collection
	 */
	public function getRules(): Collection
	{
		return $this->rules;
	}

	/**
	 * @return ModelUnitDefinition[]|Collection
	 */
	public function getUnitDefinitions(): Collection
	{
		return $this->unitDefinitions;
	}

}
