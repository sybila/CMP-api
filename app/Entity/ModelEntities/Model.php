<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use SimpleXMLElement;

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
	 * @ORM\OneToMany(targetEntity="ModelEvent", mappedBy="model", cascade={"remove"})
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
	 * @ORM\OneToMany(targetEntity="ModelParameter", mappedBy="model", cascade={"remove"})
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

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ModelDataset", mappedBy="model", cascade={"persist","remove"})
     */
    private $datasets;

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
     * Model constructor.
     */
    public function __construct()
    {
        $this->status = Model::INCOMPLETE;
        $this->isPublic = false;
        $this->datasets = new ArrayCollection([new ModelDataset($this, 'initial', true)]);
    }


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


	public function getCompartments()
	{
		return $this->compartments;
	}


	public function getConstraints()
	{
		return $this->constraints;
	}


	public function getEvents()
	{
		return $this->events;
	}


	public function getFunctionDefinitions()
	{
		return $this->functionDefinitions;
	}


	public function getInitialAssignments()
	{
		return $this->initialAssignments;
	}

	public function getParameters()
    {
//		$criteria = Criteria::create();
//		$criteria->where(Criteria::expr()->eq('reactionId', null));
		return $this->parameters; //->matching($criteria);
	}


	public function getReactions()
	{
		return $this->reactions;
	}


	public function getRules()
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
     * @return mixed
     */
    public function getDatasets()
    {
        return $this->datasets;
    }

    /**
     * @param mixed $datasets
     */
    public function setDatasets($datasets): void
    {
        $this->datasets = $datasets;
    }

    public function addDataset(ModelDataset $ds)
    {
        $this->datasets->add($ds);
    }

    public function getSpecies()
    {
        $species = new ArrayCollection();
        $this->getCompartments()->map(function (ModelCompartment $compartment) use ($species) {
            $compartment->getSpecies()->map(function (ModelSpecie $sp) use ($species) {
                $species->add($sp);
            });
        });
        return $species;
    }

    public function getReactionItems()
    {
        $rItems = new ArrayCollection();
        $this->getReactions()->map(function (ModelReaction $reaction) use ($rItems) {
            $reaction->getReactionItems()->map(function (ModelReactionItem $item) use ($rItems) {
                $rItems->add($item);
            });
        });
        return $rItems;
    }

    public function uniqueAliasCheck(string $newAlias): bool
    {
        foreach ($this->getCompartments() as $c) {
            if ($c->getAlias() === $newAlias) {
                return false;
            }
            foreach ($c->getSpecies() as $sp) {
                if ($sp->getAlias() === $newAlias) {
                    return false;
                }
            }
        }
        foreach ($this->getParameters() as $p) {
            if ($p->getAlias() === $newAlias) {
                return false;
            }
        }
        foreach ($this->getFunctionDefinitions() as $fn) {
            if ($fn->getAlias() === $newAlias) {
                return false;
            }
        }
        foreach ($this->getReactions() as $rt){
            if ($rt->getAlias() === $newAlias) {
                return false;
            }
        }
        return true;
    }


    public function getSBML()
    {
//        $domTree = new \DOMDocument('1.0', 'UTF-8');
//        $sbmlRoot = $domTree->createElement('sbml');
//        $sbmlRoot = $domTree->appendChild($sbmlRoot);
//        $model = $domTree->appendChild($domTree->createElement('model'))
//        dump($domTree->saveXML());exit();
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
            $fnDom = dom_import_simplexml($fn);
            $math = new SimpleXMLElement($fnDef->getExpression()->getContentMML());
            $mathDom = dom_import_simplexml($math);
            $mathDom = $fnDom->ownerDocument->importNode($mathDom, TRUE);
            $fnDom->appendChild($mathDom);
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
            $c->addAttribute('size', $cpt->getDefaultValue());
            $c->addAttribute('constant', $cpt->getConstant() ? 'true' : 'false');
            $cpt->getSpecies()->map(function (ModelSpecie $spec) use ($specList, $cAlias) {
                $s = $specList->addChild('species');
                $s->addAttribute('id', $spec->getAlias());
                $s->addAttribute('name', $spec->getName());
                $s->addAttribute('compartment', $cAlias);
                $s->addAttribute('initialAmount', $spec->getDefaultValue());
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
            $p->addAttribute('value', $param->getDefaultValue());
            $p->addAttribute('units', '');
            $p->addAttribute('constant', $param->getConstant() ? 'true' : 'false');
        });
        $initAssList = $sbml->addChild('listOfInitialAssignments');
        $this->initialAssignments->map(function (ModelInitialAssignment $ass) use ($initAssList) {
            $initAss = $initAssList->addChild('initialAssignment');
            $initAss->addAttribute('symbol', $ass->getAlias());
            $iADom = dom_import_simplexml($initAss);
            $math = new SimpleXMLElement($ass->getExpression()->getContentMML());
            $mathDom = dom_import_simplexml($math);
            $mathDom = $iADom->ownerDocument->importNode($mathDom, TRUE);
            $iADom->appendChild($mathDom);
        });
        $ruleList = $sbml->addChild('listOfRules');
        $this->rules->map(function (ModelRule $rul) use ($ruleList) {
            if ($rul->getType() === 'assignment') {
                $mr = $ruleList->addChild('assignmentRule');
                $mr->addAttribute('variable', $rul->getVariableAlias());
                $ruleDom = dom_import_simplexml($mr);
                $math = new SimpleXMLElement($rul->getExpression()->getContentMML());
                $mathDom = dom_import_simplexml($math);
                $mathDom = $ruleDom->ownerDocument->importNode($mathDom, TRUE);
                $ruleDom->appendChild($mathDom);
            }
            if ($rul->getType() === 'rate') {
                $mr = $ruleList->addChild('rateRule');
                $mr->addAttribute('variable', $rul->getVariableAlias());
                $ruleDom = dom_import_simplexml($mr);
                $math = new SimpleXMLElement($rul->getExpression()->getContentMML());
                $mathDom = dom_import_simplexml($math);
                $mathDom = $ruleDom->ownerDocument->importNode($mathDom, TRUE);
                $ruleDom->appendChild($mathDom);
            }
        });
        $reactList = $sbml->addChild('listOfReactions');
        $this->reactions->map(function (ModelReaction $reaction) use ($reactList) {
            $rc = $reactList->addChild('reaction');
            $rc->addAttribute('id', $reaction->getAlias());
            $rc->addAttribute('name', $reaction->getName());
            $rc->addAttribute('reversible', $reaction->getIsReversible() ? 'true' : 'false');
            $reactants = $rc->addChild('listOfReactants');
            $products = $rc->addChild('listOfProducts');
            $reaction->getReactionItems()->map(function (ModelReactionItem $rItem) use ($reactants, $products){
                if ($rItem->getType() === 'reactant') {
                    $rRef = $reactants->addChild('speciesReference');
                    $rRef->addAttribute('species', $rItem->getAlias());
                    $rRef->addAttribute('stoichiometry', $rItem->getStoichiometry());
                }
                if ($rItem->getType() === 'product') {
                    $pRef = $products->addChild('speciesReference');
                    $pRef->addAttribute('species', $rItem->getAlias());
                    $pRef->addAttribute('stoichiometry', $rItem->getStoichiometry());
                }
            });
            $reactDom = dom_import_simplexml($rc);
            $math = new SimpleXMLElement($reaction->getRate()->getContentMML());
            $mathDom = dom_import_simplexml($math);
            $mathDom = $reactDom->ownerDocument->importNode($mathDom, TRUE);
            $reactDom->appendChild($mathDom);
        });
        $eventList = $sbml->addChild('listOfEvents');
        $this->events->map(function (ModelEvent $event) use ($eventList) {
            $sbmlExent = $eventList->addChild('event');
            $sbmlExent->addAttribute('id', $event->getAlias());
            $sbmlExent->addAttribute('name', $event->getName());
            $trig = $sbmlExent->addChild('trigger');
            $trigDom = dom_import_simplexml($trig);
            $math = new SimpleXMLElement($event->getTrigger()->getContentMML());
            $mathDom = dom_import_simplexml($math);
            $mathDom = $trigDom->ownerDocument->importNode($mathDom, TRUE);
            $trigDom->appendChild($mathDom);
            $eAssList = $sbmlExent->addChild('listOfEventAssignments');
            $event->getEventAssignments()->map(function (ModelEventAssignment $eass) use ($eAssList) {
                $ass = $eAssList->addChild('eventAssignment');
                $ass->addAttribute('variable', $eass->getAlias());
            });
        });
        return $sbml->asXML();
    }

    public function getDefaultDataset()
    {
        return $this->getDatasets()->filter(function (ModelDataset $ds) {
            return $ds->getIsDefault();
        })->current();
    }



//    /**
//     * @return Experiment[]|Collection
//     */
//    public function getExperiment(): Collection
//    {
//        return $this->experiments;
//    }
//
//
//    /**
//     * @param Experiment $experiment
//     */
//    public function addExperiment(Experiment $experiment)
//    {
//        if ($this->experiments->contains($experiment)) {
//            return;
//        }
//        $this->experiments->add($experiment);
//        $experiment->addModel($this);
//    }
//
//    /**
//     * @param Experiment $experiment
//     */
//    public function removeExperiment(Experiment $experiment)
//    {
//        if (!$this->experiments->contains($experiment)) {
//            return;
//        }
//        $this->experiments->removeElement($experiment);
//        $experiment->removeModel($this);
//    }
}
