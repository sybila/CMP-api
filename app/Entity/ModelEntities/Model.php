<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Model implements IdentifiedObject
{

    const INCOMPLETE='incomplete';
    const COMPLETE='complete';
    const CURATED='curated';
    const NONCURATED='non-curated';

	use SBase;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="user_id")
	 */
	private $userId;

    /**
     * @var int
     * @ORM\Column(type="integer", name="group_id")
     */
	private $groupId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $description;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelCompartment", mappedBy="model", cascade={"remove"})
	 */
	private $compartments;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelConstraint", mappedBy="modelId", cascade={"remove"})
	 */
	private $constraints;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelEvent", mappedBy="modelId", cascade={"remove"})
	 */
	private $events;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelFunctionDefinition", mappedBy="modelId", cascade={"remove"})
	 */
	private $functionDefinitions;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelInitialAssignment", mappedBy="modelId", cascade={"remove"})
	 */
	private $initialAssignments;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelParameter", mappedBy="modelId", cascade={"remove"})
	 */
	private $parameters;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelReaction", mappedBy="modelId", cascade={"remove"})
	 */
	private $reactions;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelRule", mappedBy="modelId", cascade={"remove"})
	 */
	private $rules;

    /**
     * @ORM\Column(type="boolean",name="is_public")
     */
	private $isPublic;

    /**
     * @ORM\Column
     */
    private $status;

//	/**
//	 * @var ArrayCollection
//	 */
//	private $unitDefinitions;

//    /**
//     * @var ArrayCollection
//     * @ORM\ManyToMany(targetEntity="Experiment", inversedBy="experimentModels")
//     * @ORM\JoinTable(name="experiment_to_model", joinColumns={@ORM\JoinColumn(name="modelId", referencedColumnName="id")},
//     * inverseJoinColumns={@ORM\JoinColumn(name="experimentId", referencedColumnName="id")})
//     */
//    private $experiments;
//

	/**
	 * Get userId
	 * @return integer
	 */
	public function getUserId(): ?int
	{
		return $this->userId;
	}

	/**
	 * Set userId
	 * @param int $userId
	 * @return Model
	 */
	public function setUserId($userId): Model
	{
		$this->userId = $userId;
		return $this;
	}

    /**
     * @return int
     */
    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }

	/**
	 * Get description
	 * @return string
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * Set description
	 * @param string $description
	 * @return Model
	 */
	public function setDescription($description): Model
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Get status
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * Set status
	 * @param string $status
	 * @return Model
	 */
	public function setStatus($status): Model
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return ModelCompartment[]|Collection
	 */
	public function getCompartments(): Collection
	{
		return $this->compartments;
	}

	/**
	 * @return ModelConstraint[]|Collection
	 */
	public function getConstraints(): Collection
	{
		return $this->constraints;
	}

	/**
	 * @return ModelEvent[]|Collection
	 */
	public function getEvents(): Collection
	{
		return $this->events;
	}

	/**
	 * @return ModelFunctionDefinition[]|Collection
	 */
	public function getFunctionDefinitions(): Collection
	{
		return $this->functionDefinitions;
	}

	/**
	 * @return ModelInitialAssignment[]|Collection
	 */
	public function getInitialAssignments(): Collection
	{
		return $this->initialAssignments;
	}

	/**
	 * @return ModelParameter[]|Collection
	 */
	public function getParameters(): Collection
	{
//		$criteria = Criteria::create();
//		$criteria->where(Criteria::expr()->eq('reactionId', null));
		return $this->parameters; //->matching($criteria);
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

//	/**
//	 * @return ModelUnitDefinition[]|Collection
//	 */
//	public function getUnitDefinitions(): Collection
//	{
//		return $this->unitDefinitions;
//	}

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic
     */
    public function setIsPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }



    /**
     * @return Experiment[]|Collection
     */
    public function getExperiment(): Collection
    {
        return $this->experiments;
    }


    /**
     * @param Experiment $experiment
     */
    public function addExperiment(Experiment $experiment)
    {
        if ($this->experiments->contains($experiment)) {
            return;
        }
        $this->experiments->add($experiment);
        $experiment->addModel($this);
    }

    /**
     * @param Experiment $experiment
     */
    public function removeExperiment(Experiment $experiment)
    {
        if (!$this->experiments->contains($experiment)) {
            return;
        }
        $this->experiments->removeElement($experiment);
        $experiment->removeModel($this);
    }

    public function getSBML()
    {
        $sbml = new \SimpleXMLElement("<sbml></sbml>");
        $sbml->addAttribute('xmlns',"http://www.sbml.org/sbml/level2/version4");
        $sbml->addAttribute('level', '2');
        $sbml->addAttribute('version', "4");
        $model = $sbml->addChild('model');
        $model->addAttribute("id", $this->alias);
        $model->addAttribute("name", $this->name);

        $fnList = $sbml->addChild('listOfFunctionDefinitions');
        $this->functionDefinitions->map(function (ModelFunctionDefinition $fnDef) use ($fnList) {
            $fn = $fnList->addChild('functionDefinition');
            $fn->addAttribute('id', $fnDef->getAlias());
            $fn->addAttribute('name',$fnDef->getName());
        });
        $uUnitList = $sbml->addChild('listOfUnitDefinitions');
        $compList = $sbml->addChild('listOfCompartments');
        $specList = $sbml->addChild('listOfSpecies');
        $this->compartments->map(function (ModelCompartment $cpt) use ($compList, $specList) {
            $c = $compList->addChild('compartment');
            $cAlias = $cpt->getAlias();
            $c->addAttribute('id', $cpt->getAlias());
            $c->addAttribute('name', $cpt->getName());
            $c->addAttribute('spatialDimensions', $cpt->getSpatialDimensions());
            $c->addAttribute('size', $cpt->getSize());
            $c->addAttribute('constant', $cpt->getConstant() ? 'true' : 'false');
            $cpt->getSpecies()->map(function (ModelSpecie $spec) use ($specList, $cAlias) {
                $s = $specList->addChild('species');
                $s->addAttribute('id', $spec->getAlias());
                $s->addAttribute('name', $spec->getName());
                $s->addAttribute('compartment', $cAlias);
                $s->addAttribute('initialAmount');
                $s->addAttribute('boundaryCondition', $spec->getBoundaryCondition() ? 'true' : 'false');
                $s->addAttribute('constant', $spec->getConstant() ? 'true' : 'false');
            });
            //notes
        });
        $paraList = $sbml->addChild('listOfParameters');
        $this->parameters->map(function (ModelParameter $param) use ($paraList) {
            $p = $paraList->addChild('parameter');
            $p->addAttribute('id', $param->getAlias());
            $p->addAttribute('name', $param->getName());
            $p->addAttribute('value', $param->getValue());
            $p->addAttribute('units', '');
            $p->addAttribute('constant', $param->getIsConstant() ? 'true' : 'false');
        });
        $initAssList = $sbml->addChild('listOfInitialAssignments');
        $this->initialAssignments->map(function (ModelInitialAssignment $ass) use ($initAssList) {
            $initAss = $initAssList->addChild('initialAssignment');
            //matika
            $initAss->addAttribute('symbol', $ass->getAlias());
        });
        $ruleList = $sbml->addChild('listOfRules');
        $this->rules->map(function (ModelRule $rul) use ($ruleList) {
            if ($rul->getType() === 'assignment') {
                $mr = $ruleList->addChild('assignmentRule');
                $mr->addAttribute('variable', $rul->getVariableAlias());
                $mr->addAttribute();
            }
            if ($rul->getType() === 'rate') {
                $mr = $ruleList->addChild('rateRule');
                $mr->addAttribute('variable', $rul->getVariableAlias());
                $mr->addAttribute();
            }
        });
        $reactList = $sbml->addChild('listOfReactions');
        $this->reactions->map(function (ModelReaction $reaction) use ($reactList) {
            $rc = $reactList->addChild('reactiob');
            $rc->addAttribute();
            $rc->addAttribute();
        });
        $eventList = $sbml->addChild('listOfEvents');
        return $sbml->asXML();
    }
}
