<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Experiment implements IdentifiedObject
{
	use EBase;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="name")
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="description")
	 */
	private $description;

	/**
	 * @ORM\ManyToOne(targetEntity="Organism", inversedBy="experiments")
	 * @ORM\JoinColumn(name="organism_id", referencedColumnName="id")
	 */
	private $organismId;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="protocol")
	 */
	private $protocol;

	/**
	 * @var datetime
	 * @ORM\Column(type="datetime", name="started")
	 */
	private $started;

	/**
	 * @var datetime
	 * @ORM\Column(type="datetime", name="inserted")
	 */
	private $inserted;

	/**User neexistuje
	 * @ORM\ManyToOne(targetEntity="...", inversedBy="...")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	private $userId;

	/**
	 * @var string
	 * @ORM\Column (type="string, name="status")
	 */
	private $status;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ExperimentVariable", mappedBy="experimentId")
	 */
	private $variables;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ExperimentNote", mappedBy="experimentId")
	 */
	private $notes;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="ExperimentDevice", mappedBy="experimentId")
	 */
	private $devices;


	/**
	 * Get name
	 * @return string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Set name
	 * @param string $name
	 * @return Experiment
	 */
	public function setName($name): Experiment
	{
		$this->name = $name;
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
	 * @return Experiment
	 */
	public function setDescription($description): Experiment
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Get protocol
	 * @return string
	 */
	public function getProtocol(): ?string
	{
		return $this->protocol;
	}

	/**
	 * Set protocol
	 * @param string $protocol
	 * @return Experiment
	 */
	public function setProtocol($protocol): Experiment
	{
		$this->protocol = $protocol;
		return $this;
	}

	/**
	 * Get started
	 * @return datetime
	 */
	public function getStarted(): ?datetime
	{
		return $this->started;
	}

	/**
	 * Set started
	 * @param datetime $started
	 * @return Experiment
	 */
	public function setStarted($started): Experiment
	{
		$this->started = $started;
		return $this;
	}

	/**
	 * Get inserted
	 * @return datetime
	 */
	public function getInserted(): ?datetime
	{
		return $this->inserted;
	}

	/**
	 * Set inserted
	 * @param datetime $inserted
	 * @return Experiment
	 */
	public function setInserted($inserted): Experiment
	{
		$this->inserted = $inserted;
		return $this;
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
	 * @return Experiment
	 */
	public function setUserId($userId): Experiment
	{
		$this->userId = $userId;
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
	 * @return Experiment
	 */
	public function setStatus($status): Experiment
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return ExperimentVariable[]|Collection
	 */
	public function getVariables(): Collection
	{
		return $this->variables;
	}

	/**
	 * @return ExperimentNote[]|Collection
	 */
	public function getNote(): Collection
	{
		return $this->notes;
	}

	//ExperimentRelation
}