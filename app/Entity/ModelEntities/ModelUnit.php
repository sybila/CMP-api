<?php

namespace App\Entity;


use App\Exceptions\EntityClassificationException;
use App\Exceptions\EntityHierarchyException;
use App\Exceptions\EntityLocationException;
use App\Helpers\
{
	ChangeCollection, ConsistenceEnum
};
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\Tests\StringClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_unit")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelUnit implements IdentifiedObject
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

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
	private $name;

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
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}


	/**
	 * Get name
	 *
	 * @return null|string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}


	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return ModelUnit
	 */
	public function setName($name): ModelUnit
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get symbol
	 *
	 * @return null|string
	 */
	public function getSymbol(): ?string
	{
		return $this->symbol;
	}


	/**
	 * Set symbol
	 *
	 * @param string $symbol
	 *
	 * @return ModelUnit
	 */
	public function setSymbol($symbol): ModelUnit
	{
		$this->symbol = $symbol;
		return $this;
	}

	/**
	 * Get baseUnitId
	 *
	 * @return integer
	 */
	public function getBaseUnitId(): ?int
	{
		return $this->baseUnitId;
	}

	/**
	 * Set baseUnitId
	 *
	 * @param int $baseUnitId
	 *
	 * @return ModelUnit
	 */
	public function setApprovedId($baseUnitId): ModelUnit
	{
		$this->baseUnitId = $baseUnitId;
		return $this;
	}

	/**
	 * Get exponent
	 *
	 * @return float
	 */
	public function getExponent(): ?float
	{
		return $this->exponent;
	}


	/**
	 * Set exponent
	 *
	 * @param float $exponent
	 *
	 * @return ModelUnit
	 */
	public function setExponent($exponent): ModelUnit
	{
		$this->exponent = $exponent;
		return $this;
	}

	/**
	 * Get multiplier
	 *
	 * @return float
	 */
	public function getMultiplier(): ?float
	{
		return $this->multiplier;
	}

	/**
	 * Set multiplier
	 *
	 * @param float $multiplier
	 *
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
