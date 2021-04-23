<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_compartment")
 */
class ModelCompartment implements IdentifiedObject
{
	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $model;

	/**
     * //FIXME closely interconnected with units, when units are done we need to look at this again
	 * @var float
	 * @ORM\Column(name="spatial_dimensions",type="float",nullable=true)
	 */
	protected $spatialDimensions;

	/**
	 * @var boolean
	 * @ORM\Column(name="is_constant",type="integer")
	 */
	protected $constant;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelSpecie", mappedBy="compartmentId",cascade={"remove"})
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
     * @ORM\OneToMany(targetEntity="ModelVarToDataset", mappedBy="compartment", cascade={"persist", "remove"})
     */
	protected $inDatasets;

    /**
     * @ORM\OneToMany(targetEntity="ModelEventAssignment", mappedBy="compartment", cascade={"remove"})
     */
    protected $eventAssignments;


    /**
     * ModelCompartment constructor.
     */
    public function __construct(Model $model, $value)
    {
        $this->setModel($model);
        $this->inDatasets = new ArrayCollection();
        $var = new ModelVarToDataset();
        $var->setCompartment($this);
        $var->setVarType('compartment');
        $var->setValue($value);
        foreach ($this->model->getDatasets() as $ds){
            /** @var ModelDataset $ds */
            $var->setDataset($ds);
            $this->inDatasets->add($var);
        }
    }

	/**
	 * Get model
	 */
	public function getModel()
	{
		return $this->model;
	}


	public function setModel($model)
	{
		$this->model = $model;
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
	 */
	public function setSpatialDimensions(int $spatialDimensions)
	{
		$this->spatialDimensions = $spatialDimensions;
	}


	public function getSize()
	{
	    return $this->getDefaultValue();
	}


	public function setSize(string $size)
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
	 */
	public function setConstant(int $constant)
	{
		$this->constant = $constant;
	}

    public function setIsConstant2(int $isConstant)
    {
        $this->constant = $isConstant;
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
     * @return mixed
     */
    public function getInDatasets()
    {
        return $this->inDatasets;
    }

    /**
     * @param mixed $datasets
     */
    public function setInDatasets($datasets): void
    {
        $this->inDatasets = $datasets;
    }

    /**
     * Gets value of the size of compartments from dataset that is default.
     */
    public function getDefaultValue()
    {
        /** @var ModelDataset $ds */
        $ds = $this->getModel()->getDatasets()->filter(function (ModelDataset $dataset) {
            return $dataset->getIsDefault();
        })->current();
        $res = 0;
        $ds->getDatasetVariableValue('compartment', $this->getId(), $res);
        return $res;
    }

    /**
     * @return mixed
     */
    public function getEventAssignments()
    {
        return $this->eventAssignments;
    }


}
