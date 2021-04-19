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

	public function getParameters()
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
