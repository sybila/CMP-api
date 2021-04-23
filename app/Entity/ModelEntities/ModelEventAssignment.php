<?php

namespace App\Entity;

use App\Exceptions\ConstantVariableException;
use App\Exceptions\DependentAttributeBoundException;
use App\Exceptions\DependentAttributeException;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\MissingRequiredKeyException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Radoslav Doktor from 2018
 * @author Marek Havlik to 2018
 * SBML equivalent of EventAssignment
 * @ORM\Entity
 * @ORM\Table(name="model_event_assignment")
 */
class ModelEventAssignment implements IdentifiedObject
{
	use SBase;

    const VARIABLE_TYPES = ['species', 'compartment', 'reactionItem', 'parameter'];

	/**
     * Parent event
     * @var ModelEvent
	 * @ORM\ManyToOne(targetEntity="ModelEvent", inversedBy="eventAssignments")
	 * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
	 */
	protected $event;

    /**
     * SBML equivalent of 'math', with ability to generate LaTEX
	 * @ORM\OneToOne(targetEntity="MathExpression", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="formula", referencedColumnName="id", nullable=true)
	 */
	protected $formula;

    /**
     * @var ModelCompartment
     * @ORM\ManyToOne(targetEntity="ModelCompartment", inversedBy="eventAssignments")
     * @ORM\JoinColumn(name="compartment_id", referencedColumnName="id")
     */
	protected $compartment;

    /**
     * @var ModelSpecie
     * @ORM\ManyToOne(targetEntity="ModelSpecie", inversedBy="eventAssignments")
     * @ORM\JoinColumn(name="species_id", referencedColumnName="id")
     */
    protected $species;

    /**
     * @var ModelReactionItem
     * @ORM\ManyToOne(targetEntity="ModelReactionItem", inversedBy="eventAssignments")
     * @ORM\JoinColumn(name="reaction_item_id", referencedColumnName="id")
     */
    protected $reactionItem;

    /**
     * @var ModelParameter
     * @ORM\ManyToOne(targetEntity="ModelParameter", inversedBy="eventAssignments")
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id")
     */
    protected $parameter;

    /**
     * Variable can be compartments/species/reactionItem(speciesRef in sbml) or parameter
     * @ORM\Column(name="variable_type")
     */
    protected $variableType;

    /**
     * ModelEventAssignment constructor.
     */
    public function __construct()
    {
        $this->formula = new MathExpression();
    }

    /**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get eventId
	 */
	public function getEvent()
	{
		return $this->event;
	}

	public function setEvent($event)
	{
		$this->event = $event;
	}

    public function getFormula()
    {
        return $this->formula;
    }

    public function setFormula(string $mathType, string $formula): void
    {
        /** @var MathExpression $expr */
        $expr = $this->getFormula();
        if ($mathType === 'latex') {
            $expr->setLatex($formula);
        } elseif ($mathType === 'cmml') {
            $expr->setContentMML($formula, true);
        }
        $this->formula = $expr;
    }

    public function getVariable()
    {
        if ($this->getVariableType() === 'species'){
            return $this->getSpecies();
        } elseif ($this->getVariableType() === 'compartment'){
            return $this->getCompartment();
        } elseif ($this->getVariableType() === 'parameter'){
            return $this->getParameter();
        } elseif ($this->getVariableType() === 'reactionItem'){
            return $this->getReactionItem();
        }
        return null;
    }

    /**
     * @param mixed $variableId
     */
    public function setVariable(int $variableId, EntityManager $orm): void
    {
        if ($this->variableType === null) {
            throw new DependentAttributeException('variableType');
        }
        $variable = null;
        /** @var Model $model */
        $model = $this->event->getModel();
        if ($this->getVariableType() === 'species') {
            /** @var ModelSpecie $variable */
            $variable = $orm->getRepository(ModelSpecie::class)
                ->findOneBy(['id' => $variableId, 'model' => $model]);
            $this->setSpecies($variable);
        } elseif ($this->getVariableType() === 'compartment') {
            /** @var ModelCompartment $variable */
            $variable = $orm->getRepository(ModelCompartment::class)
                ->findOneBy(['id' => $variableId, 'model' => $model]);
            $this->setCompartment($variable);
        } elseif ($this->getVariableType() === 'parameter') {
            /** @var ModelParameter $variable */
            $variable = $orm->getRepository(ModelParameter::class)
                ->findOneBy(['id' => $variableId, 'model' => $model]);
            $this->setParameter($variable);
        } elseif ($this->getVariableType() === 'reactionItem') {
            /** @var ModelReactionItem $variable */
            $variable = $orm->getRepository(ModelReactionItem::class)
                ->findOneBy(['id' => $variableId, 'model' => $model]);
            $this->setReactionItem($variable);
        }
        if (is_null($variable)) {
            throw new InvalidArgumentException('variableId', 'variable', 'non-existing variable');
        }

//        if ($variable->getConstant()) {
//            throw new ConstantVariableException($variableId, $this->variableType, $variable->getAlias());
//        }
    }

    /**
     * @return mixed
     */
    public function getVariableType()
    {
        return $this->variableType;
    }

    /**
     * @param mixed $variableType
     */
    public function setVariableType($variableType): void
    {
        $this->variableType = $variableType;
    }

    /**
     * @return ModelCompartment
     */
    public function getCompartment(): ModelCompartment
    {
        return $this->compartment;
    }

    /**
     * @param ModelCompartment $compartment
     */
    public function setCompartment(ModelCompartment $compartment): void
    {
        $this->compartment = $compartment;
    }

    /**
     * @return ModelSpecie
     */
    public function getSpecies(): ModelSpecie
    {
        return $this->species;
    }

    /**
     * @param ModelSpecie $species
     */
    public function setSpecies(ModelSpecie $species): void
    {
        $this->species = $species;
    }

    /**
     * @return ModelReactionItem
     */
    public function getReactionItem(): ModelReactionItem
    {
        return $this->reactionItem;
    }

    /**
     * @param ModelReactionItem $reactionItem
     */
    public function setReactionItem(ModelReactionItem $reactionItem): void
    {
        $this->reactionItem = $reactionItem;
    }

    /**
     * @return ModelParameter
     */
    public function getParameter(): ModelParameter
    {
        return $this->parameter;
    }

    /**
     * @param ModelParameter $parameter
     */
    public function setParameter(ModelParameter $parameter): void
    {
        $this->parameter = $parameter;
    }




}
