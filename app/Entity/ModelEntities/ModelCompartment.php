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
	 * @var float
	 * @ORM\Column(name="size",type="float",nullable=true)
	 */
	protected $size;

	/**
	 * @var boolean
	 * @ORM\Column(name="is_constant",type="integer")
	 */
	protected $constant;

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
     * @ORM\OneToMany(targetEntity="ModelVarToDataset", mappedBy="compartment", cascade={"persist", "remove"})
     */
	protected $inDatasets;

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
	 */
	public function setSize(int $size)
	{
		$this->size = $size;
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
	 * @return ModelCompartment
	 */
	public function setConstant(int $constant): ModelCompartment
	{
		$this->constant = $constant;
		//FIXME this one modus operandi is good if we want to chain the "set" methods
        //FIXME but why, if we never use it. It is more transparent, but it is slower.
		return $this;
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

    public function getDefaultValue() : int
    {
        //TODO get rid of value property (getValue)
        /** @var ModelDataset $ds */
        $ds = $this->getModel()->getDatasets()->filter(function (ModelDataset $dataset) {
            return $dataset->getIsDefault();
        })->current();
        $res = $this->getSize();
        $ds->getDatasetVariableValue('compartment', $this->getId(), $res);

        return $res;
    }

}
