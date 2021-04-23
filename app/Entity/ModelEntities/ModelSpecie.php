<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var Model
     * @ORM\ManyToOne(targetEntity="Model", inversedBy="parameters")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     */
	protected $model;

	/**
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
	 * @ORM\OneToMany(targetEntity="ModelRule", mappedBy="specie")
	 */
	protected $rules;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ModelVarToDataset", mappedBy="species", cascade={"persist", "remove"})
     */
	protected $inDatasets;

    /**
     * @ORM\OneToMany(targetEntity="ModelEventAssignment", mappedBy="species", cascade={"remove"})
     */
    protected $eventAssignments;

    /**
     * ModelSpecie constructor.
     */
    public function __construct(Model $model, $value)
    {
        $this->setModel($model);
        $this->inDatasets = new ArrayCollection();
        $var = new ModelVarToDataset();
        $var->setSpecies($this);
        $var->setVarType('species');
        $var->setValue($value);
        foreach ($this->model->getDatasets() as $ds){
        /** @var ModelDataset $ds */
            $var->setDataset($ds);
            $this->inDatasets->add($var);
        }
    }

    public function getModel()
	{
		return $this->model;
	}


	public function setModel($model)
	{
		$this->model = $model;
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

    /**
     * @return mixed
     */
    public function getInDatasets()
    {
        return $this->inDatasets;
    }

    /**
     * @param mixed $inDatasets
     */
    public function setInDatasets($inDatasets): void
    {
        $this->inDatasets = $inDatasets;
    }

    public function getDefaultValue()
    {
        /** @var ModelDataset $ds */
        $ds = $this->getModel()->getDatasets()->filter(function (ModelDataset $dataset) {
            return $dataset->getIsDefault();
        })->current();
        $res = 0;
        $ds->getDatasetVariableValue('species', $this->getId(), $res);
        return $res;
    }

    public function setDefaultValue(string $size)
    {
        /** @var ModelDataset $ds */
        $dsId = $this->getModel()->getDatasets()->filter(function (ModelDataset $dataset) {
            return $dataset->getIsDefault();
        })->current()->getId();
        $this->inDatasets->filter(function (ModelVarToDataset $varToDataset) use ($dsId) {
            return $varToDataset->getDataset()->getId() === $dsId;
        })->current()->setValue($size);
    }

    /**
     * @return mixed
     */
    public function getEventAssignments()
    {
        return $this->eventAssignments;
    }


}
