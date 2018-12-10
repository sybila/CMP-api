<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_unit_definition")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelUnitDefinition implements IdentifiedObject
{
	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

	/**
	 * @ORM\ManyToMany(targetEntity="ModelUnit")
	 * @ORM\JoinTable(name="model_unit_to_definition",
	 *     joinColumns={@ORM\JoinColumn(name="model_unit_definition_id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="model_unit_id")}
	 * )
	 * @var ArrayCollection
	 */
	protected $units;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $symbol;

	/**
	 * @ORM\ManyToOne(targetEntity="ModelCompartment", inversedBy="units")
	 * @ORM\JoinColumn(name="model_compartment_id", referencedColumnName="id")
	 */
	protected $compartmentId;

	/**
	 * @ORM\ManyToOne(targetEntity="ModelCompartment", inversedBy="units")
	 * @ORM\JoinColumn(name="model_compartment_id", referencedColumnName="id")
	 */
	protected $localParameterId;

	/**
	 * @ORM\ManyToOne(targetEntity="ModelCompartment", inversedBy="units")
	 * @ORM\JoinColumn(name="model_compartment_id", referencedColumnName="id")
	 */
	protected $parameterId;

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
	 * @return ModelUnitDefinition
	 */
	public function setModelId($modelId): ModelUnitDefinition
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get symbol
	 * @return null|string
	 */
	public function getSymbol(): ?string
	{
		return $this->symbol;
	}

	/**
	 * Set symbol
	 * @param string $symbol
	 * @return ModelUnitDefinition
	 */
	public function setSymbol($symbol): ModelUnitDefinition
	{
		$this->symbol = $symbol;
		return $this;
	}

	/**
	 * Get compartmentId
	 * @return integer|null
	 */
	public function getCompartmentId()
	{
		return $this->compartmentId;
	}

	/**
	 * Set compartmentId
	 * @param integer $compartmentId
	 * @return ModelUnitDefinition
	 */
	public function setCompartmentId($compartmentId): ModelUnitDefinition
	{
		$this->compartmentId = $compartmentId;
		return $this;
	}

	/**
	 * Get parameterId
	 * @return integer|null
	 */
	public function getParameterId()
	{
		return $this->parameter;
	}

	/**
	 * Set parameter
	 * @param integer $parameter
	 * @return ModelUnitDefinition
	 */
	public function setParameter($parameterId): ModelUnitDefinition
	{
		$this->parameterId = $parameterId;
		return $this;
	}

	/**
	 * Get localParameterId
	 * @return integer|null
	 */
	public function getLocalParameterId()
	{
		return $this->localParameter;
	}

	/**
	 * Set localParameter
	 * @param integer $localParameter
	 * @return ModelUnitDefinition
	 */
	public function setLocalParameter($localParameterId): ModelUnitDefinition
	{
		$this->localParameterId = $localParameterId;
		return $this;
	}

	/**
	 * @return ModelUnit[]|Collection
	 */
	public function getUnits(): Collection
	{
		return $this->units;
	}

}
