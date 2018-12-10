<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_unit")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelUnit implements IdentifiedObject
{
	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="ModelUnit", inversedBy="referencedBy")
	 * @ORM\JoinColumn(name="base_model_unit_id", referencedColumnName="id")
	 */
	private $baseUnitId;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelUnit", mappedBy="baseUnitId")
	 */
	private $referencedBy;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $symbol;

	/**
	 * @var float
	 * @ORM\Column(type="float")
	 */
	private $exponent;

	/**
	 * @var float
	 * @ORM\Column(type="float")
	 */
	private $multiplier;

	/**
	 * @ORM\ManyToMany(targetEntity="ModelUnitDefinition")
	 * @ORM\JoinTable(name="model_unit_to_definition",
	 *     joinColumns={@ORM\JoinColumn(name="model_unit_id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="model_unit_definition_id")}
	 * )
	 * @var ArrayCollection
	 */
	protected $definitions;

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
	 * @return ModelUnit
	 */
	public function setSymbol($symbol): ModelUnit
	{
		$this->symbol = $symbol;
		return $this;
	}

	/**
	 * Get baseUnitId
	 * @return integer
	 */
	public function getBaseUnitId(): ?int
	{
		return $this->baseUnitId;
	}

	/**
	 * Set baseUnitId
	 * @param int $baseUnitId
	 * @return ModelUnit
	 */
	public function setApprovedId($baseUnitId): ModelUnit
	{
		$this->baseUnitId = $baseUnitId;
		return $this;
	}

	/**
	 * Get exponent
	 * @return float
	 */
	public function getExponent(): ?float
	{
		return $this->exponent;
	}

	/**
	 * Set exponent
	 * @param float $exponent
	 * @return ModelUnit
	 */
	public function setExponent($exponent): ModelUnit
	{
		$this->exponent = $exponent;
		return $this;
	}

	/**
	 * Get multiplier
	 * @return float
	 */
	public function getMultiplier(): ?float
	{
		return $this->multiplier;
	}

	/**
	 * Set multiplier
	 * @param float $multiplier
	 * @return ModelUnit
	 */
	public function setMultiplier($multiplier): ModelUnit
	{
		$this->multiplier = $multiplier;
		return $this;
	}

	/**
	 * @return ModelUnitDefinition[]|Collection
	 */
	public function getReferencedBy(): Collection
	{
		return $this->referencedBy;
	}
}
