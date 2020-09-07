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
	 * @var int
	 * @ORM\Column(type="integer", name="approved_id")
	 */
	private $approvedId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $description;

	/**
	 * @var string
	 * @ORM\Column (type="string")
	 */
	private $status;

	/**
     * @var string
     * @ORM\Column (type="string")
     */
	private $origin;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelCompartment", mappedBy="modelId")
	 */
	private $compartments;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelConstraint", mappedBy="modelId")
	 */
	private $constraints;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelEvent", mappedBy="modelId")
	 */
	private $events;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelFunctionDefinition", mappedBy="modelId")
	 */
	private $functionDefinitions;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelInitialAssignment", mappedBy="modelId")
	 */
	private $initialAssignments;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelParameter", mappedBy="modelId")
	 */
	private $parameters;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelReaction", mappedBy="modelId")
	 */
	private $reactions;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelRule", mappedBy="modelId")
	 */
	private $rules;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelUnitDefinition", mappedBy="modelId")
	 */
	private $unitDefinitions;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Experiment", inversedBy="experimentModels")
     * @ORM\JoinTable(name="experiment_to_model", joinColumns={@ORM\JoinColumn(name="modelId", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="experimentId", referencedColumnName="id")})
     */
    private $experiments;

    public function getAlias(){
        return 'm';
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
	 * Get approvedId
	 * @return integer
	 */
	public function getApprovedId(): ?int
	{
		return $this->approvedId;
	}

	/**
	 * Set approvedId
	 * @param int $approvedId
	 * @return Model
	 */
	public function setApprovedId($approvedId): Model
	{
		$this->approvedId = $approvedId;
		return $this;
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
	public function getStatus(): ?string
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
     * @return string
     */
    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return Model
     */
    public function setOrigin(string $origin): Model
    {
        $this->origin = $origin;
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
	 * @return ModelFunctinoDefinition[]|Collection
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
		$criteria = Criteria::create();
		$criteria->where(Criteria::expr()->eq('reactionId', null));
		return $this->parameters->matching($criteria);
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
	 * @return ModelUnitDefinition[]|Collection
	 */
	public function getUnitDefinitions(): Collection
	{
		return $this->unitDefinitions;
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
}
