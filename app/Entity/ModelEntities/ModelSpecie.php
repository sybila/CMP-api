<?php

namespace App\Entity;


use App\Exceptions\EntityClassificationException;
use App\Exceptions\EntityHierarchyException;
use App\Exceptions\EntityLocationException;
use App\Helpers\
{
	ChangeCollection, ConsistenceEnum
};
use App\Exceptions\EntityException;
use Consistence\Enum\InvalidEnumValueException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\Tests\StringClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_specie")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelSpecie implements IdentifiedObject
{


	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

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
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="equation_type")
	 */
	protected $equationType;

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
	protected $isConstant;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelReactionItem", mappedBy="specieId")
	 */
	protected $reactionItems;

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
	 * Get modelId
	 *
	 * @return integer
	 */
	public function getModelId()
	{
		return $this->modelId;
	}


	/**
	 * Set modelId
	 *
	 * @param integer $modelId
	 *
	 * @return ModelSpecie
	 */
	public function setModelId($modelId): ModelSpecie
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get compartmentId
	 *
	 * @return integer
	 */
	public function getCompartmentId()
	{
		return $this->compartmentId;
	}

	/**
	 * Set compartmentId
	 *
	 * @param integer $compartmentId
	 *
	 * @return ModelSpecie
	 */
	public function setCompartmentId($compartmentId): ModelSpecie
	{
		$this->compartmentId = $compartmentId;
		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
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
	 * @return ModelSpecie
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}


	/**
	 * Get equationType
	 *
	 * @return string
	 */
	public function getEquationType(): ?string
	{
		return $this->equationType;
	}

	/**
	 * Set equationType
	 *
	 * @param string $equationType
	 *
	 * @return ModelSpecie
	 */
	public function setEquationType($equationType): ModelSpecie
	{
		$this->equationType = $equationType;
		return $this;
	}

	/**
	 * Get initialExpression
	 *
	 * @return string
	 */
	public function getInitialExpression(): ?string
	{
		return $this->initialExpression;
	}

	/**
	 * Set initialExpression
	 *
	 * @param string $initialExpression
	 *
	 * @return Model
	 */
	public function setInitialExpression($initialExpression): ModelSpecie
	{
		$this->initialExpression = $initialExpression;
		return $this;
	}



	/**
	 * Get boundaryCondition
	 *
	 * @return integer
	 */
	public function getBoundaryCondition(): ?int
	{
		return $this->boundaryCondition;
	}

	/**
	 * Set boundaryCondition
	 *
	 * @param integer $boundaryCondition
	 *
	 * @return ModelSpecie
	 */
	public function setBoundaryCondition($boundaryCondition): ModelSpecie
	{
		$this->boundaryCondition = $boundaryCondition;
		return $this;
	}

	/**
	 * Get hasOnlySubstanceUnits
	 *
	 * @return integer
	 */
	public function getHasOnlySubstanceUnits(): int
	{
		return $this->hasOnlySubstanceUnits;
	}

	/**
	 * Set hasOnlySubstanceUnits
	 *
	 * @param integer $hasOnlySubstanceUnits
	 *
	 * @return ModelSpecie
	 */
	public function setHasOnlySubstanceUnits($hasOnlySubstanceUnits): ModelSpecie
	{
		$this->hasOnlySubstanceUnits = $hasOnlySubstanceUnits;
		return $this;
	}


	/**
	 * Get isConstant
	 *
	 * @return integer
	 */
	public function getIsConstant(): int
	{
		return $this->isConstant;
	}

	/**
	 * Set isConstant
	 *
	 * @param integer $isConstant
	 *
	 * @return ModelSpecie
	 */
	public function setIsConstant($isConstant): ModelSpecie
	{
		$this->isConstant = $isConstant;
		return $this;
	}

	/**
	 * @return Collection []
	 */
	public function getReactionItems(): Collection
	{
		return $this->reactionItems;
	}


}
