<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_parameter")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelParameter implements IdentifiedObject
{

	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="parameters")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $model;

	/**
	 * @ORM\ManyToOne(targetEntity="ModelReaction", inversedBy="parameters")
	 * @ORM\JoinColumn(name="model_reaction_id", referencedColumnName="id")
	 */
	protected $reaction;

	/**
	 * @var boolean
	 * @ORM\Column(name="is_constant",type="integer")
	 */
	protected $constant;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelReactionItem", mappedBy="parameterId")
	 */
	protected $reactionItems;

	/**
     * @ORM\OneToOne(targetEntity="ModelRule", mappedBy="parameter")
	 */
	protected $rule;

    /**
     * @ORM\OneToMany(targetEntity="ModelVarToDataset", mappedBy="parameter", cascade={"persist", "remove"})
     */
	protected $inDatasets;

    /**
     * @ORM\OneToMany(targetEntity="ModelEventAssignment", mappedBy="parameter", cascade={"remove"})
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
        $var->setParameter($this);
        $var->setVarType('parameter');
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
	 * Get reactionId
	 * @return integer|null
	 */
	public function getReaction()
	{
		return $this->reaction;
	}

	public function setReaction($reaction)
	{
		$this->reaction = $reaction;
	}


	public function getValue()
	{
		return $this->getDefaultValue();
	}


	public function setValue($value)
	{
        /** @var ModelDataset $ds */
        $dsId = $this->getModel()->getDatasets()->filter(function (ModelDataset $dataset) {
            return $dataset->getIsDefault();
        })->current()->getId();
        $this->inDatasets->filter(function (ModelVarToDataset $varToDataset) use ($dsId) {
            return $varToDataset->getDataset()->getId() === $dsId;
        })->current()->setValue($value);
	}


	public function getConstant()
	{
		return $this->constant;
	}


	public function setConstant($constant)
	{
		$this->constant = $constant;
	}

	/**
	 * @return ModelReactionItem[]|Collection
	 */
	public function getReactionsItems(): Collection
	{
		return $this->reactionItems;
	}

	public function getRule()
	{
		return $this->rule;
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
        $ds->getDatasetVariableValue('parameter', $this->getId(), $res);

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
