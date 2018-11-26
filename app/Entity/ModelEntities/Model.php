<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Model implements IdentifiedObject
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="user_id")
	 */
	private $userId;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="approved_id")
	 */
	private $approvedId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="sbml_id")
	 */
	private $sbmlId;

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
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
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
	 * Get name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set name
	 * @param string $name
	 * @return Model
	 */
	public function setName($name): Model
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get sbmlId
	 * @return string
	 */
	public function getSbmlId()
	{
		return $this->sbmlId;
	}

	/**
	 * Set sbmlId
	 * @param string $sbmlId
	 * @return Model
	 */
	public function setSbmlId($sbmlId): Model
	{
		$this->sbmlId = $sbmlId;
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
		return $this->parameters;
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

}
