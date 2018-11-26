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
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="sbml_id")
	 */
	private $sbmlId;

	/**
	 * @var int
	 * @ORM\Column(name="spatial_dimensions",type="integer",nullable=true)
	 */
	protected $spatialDimensions;

	/**
	 * @var int
	 * @ORM\Column(name="size",type="integer",nullable=true)
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
	 * @return ModelCompartment
	 */
	public function setModelId($modelId): ModelCompartment
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get name
	 * @return null|string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Set name
	 * @param string $name
	 * @return ModelCompartment
	 */
	public function setName($name): ModelCompartment
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get sbmlId
	 * @return string
	 */
	public function getSbmlId()
	{
		return $this->sbmlId;
	}

	/**
	 * Set sbmlId
	 * @param string $sbmlId
	 * @return Model
	 */
	public function setSbmlId($sbmlId): ModelCompartment
	{
		$this->sbmlId = $sbmlId;
		return $this;
	}

	/**
	 * Get spatialDimensions
	 * @return int|null
	 */
	public function getSpatialDimensions(): ?int
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
	 * @return int|null
	 */
	public function getSize(): ?int
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
