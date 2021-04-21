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
	protected $reactionId;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $value;

	/**
	 * @var boolean
	 * @ORM\Column(name="is_constant",type="integer")
	 */
	protected $isConstant;

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
     * @ORM\OneToMany(targetEntity="ModelVarToDataset", mappedBy="parameter")
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
	public function getReactionId()
	{
		return $this->reactionId;
	}

	public function setReactionId($reactionId)
	{
		$this->reactionId = $reactionId;
	}

	/**
	 * Get value
	 * @return integer
	 */
	public function getValue(): ?int
	{
		return $this->value;
	}


	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Get isConstant
	 * @return integer
	 */
	public function getIsConstant(): int
	{
		return $this->isConstant;
	}


	public function setIsConstant($isConstant)
	{
		$this->isConstant = $isConstant;
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

    public function getDefaultValue() : int
    {
        //TODO get rid of value property (getValue)
        /** @var ModelDataset $ds */
        $ds = $this->getModel()->getDatasets()->filter(function (ModelDataset $dataset) {
            return $dataset->getIsDefault();
        })->current();
        $res = $this->getValue();
        $ds->getDatasetVariableValue('parameter', $this->getId(), $res);

        return $res;
    }
}
