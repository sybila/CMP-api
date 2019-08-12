<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use App\Helpers\DateTimeJson;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Experiment implements IdentifiedObject
{

    const STATUS_PUBLIC = 'public';
    const STATUS_PRIVATE = 'private';

	use EBase;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
	 * @ORM\ManyToOne(targetEntity="App\Entity\Organism", inversedBy="experiments", fetch="EAGER")
	 * @ORM\JoinColumn(name="organism_id", referencedColumnName="id")
	 */
	private $organismId;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="protocol")
	 */
	private $protocol;

	/**
	 * @var DateTimeJson
	 * @ORM\Column(type="datetime", name="started")
	 */
	private $started;

	/**
	 * @var DateTimeJson
	 * @ORM\Column(type="datetime", name="inserted")
	 */
	private $inserted;

	/**User neexistuje
	 * @ORM\ManyToOne(targetEntity="...", inversedBy="...")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	//private $userId;

	/**
	 * @var string
     * @ORM\Column(type="string", columnDefinition="ENUM('private', 'public')")
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
     * @ORM\OneToMany(targetEntity="ExperimentRelation", mappedBy="firstExperimentId", fetch="EAGER")
     */
    private $experimentRelation;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ExperimentModels", mappedBy="ExperimentId", fetch="EAGER")
     */
    private $experimentModels;

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
	 * @param integer $description
	 * @return Experiment
	 */
	public function setDescription($description): Experiment
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Set organismId
	 * @param integer $organismId
	 * @return Experiment
	 */
	public function setOrganismId($organismId): Experiment
	{
		$this->organismId = $organismId;
		return $this;
	}

	/**
	 * Get organismId
	 * @return Organism
	 */
	public function getOrganismId(): ?Organism
	{
		return $this->organismId;
	}
	/**
	 * Get protocol
	 * @return string|null
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
	 * @return DateTimeJson|null
	 */
	public function getStarted(): ?DateTimeJson
	{
		return $this->started;
	}

	/**
	 * Set started
	 * @param DateTimeJson $started
	 * @return Experiment
	 */
	public function setStarted($started): Experiment
	{
		$this->started = $started;
		return $this;
	}

	/**
	 * Get inserted
	 * @return DateTimeJson|null
	 */
	public function getInserted(): ?DateTimeJson
	{
		return $this->inserted;
	}

	/**
	 * Set inserted
	 * @param DateTimeJson $inserted
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
	public function getStatus(): string
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
        if (!in_array($status, array(self::STATUS_PUBLIC, self::STATUS_PRIVATE))) {
            throw new \InvalidArgumentException("Invalid status");
        }
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

    /**
     * @return ExperimentRelation[]|Collection
     */
    public function getExperimentRelation(): Collection
    {
        return $this->experimentRelation;
    }

    /**
     * @return ExperimentDevice[]|Collection
     */
    public function getExperimentDevices(): Collection
    {
        return $this->devices;
    }

    /**
     * @return ExperimentRelation[]|Collection
     */
    public function getExperimentModels(): Collection
    {
        return $this->experimentModels;
    }
}