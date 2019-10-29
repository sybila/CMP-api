<?php

namespace App\Entity;

use DateTime;
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
     * @ORM\ManyToMany(targetEntity="Experiment", inversedBy="experimentRelation")
     * @ORM\JoinTable(name="experiment_to_experiment", joinColumns={@ORM\JoinColumn(name="1exp_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="2exp_id", referencedColumnName="id")})
     */
    private $experimentRelation;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Model", inversedBy="experiments")
     * @ORM\JoinTable(name="experiment_to_model", joinColumns={@ORM\JoinColumn(name="experimentId", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="modelId", referencedColumnName="id")})
     */
    private $experimentModels;

    /**
	 * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Device", inversedBy="experiments")
     * @ORM\JoinTable(name="experiment_device", joinColumns={@ORM\JoinColumn(name="exp_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="dev_id", referencedColumnName="id")})
	 */
	private $devices;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Bioquantity", inversedBy="experiments")
     * @ORM\JoinTable(name="bioquantities_experiment", joinColumns={@ORM\JoinColumn(name="exp_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="bionum_id", referencedColumnName="id")})
     */
    private $bioquantities;


    public function __construct()
    {
        $this->inserted = new DateTimeJson;
        $this->started = new DateTimeJson;
        $this->devices = new ArrayCollection();
        $this->bioquantities = new ArrayCollection();
    }

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
	 * @param Organism $organismId
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
	 * @return DateTimeJson
	 */
	public function getStarted(): DateTimeJson
	{
		return $this->started;
	}

    /**
     * Set started
     * @param string $started
     * @return Experiment
     */
	public function setStarted(string $started): Experiment
	{
		$this->started = date_create_from_format('d/m/Y:H:i:s', $started);
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

	/*/**
	 * Set inserted
	 * @return Experiment
	 */
	/*public function setInserted(): Experiment
	{
		$this->inserted = new DateTimeJson();
		return $this;
	}*/


    /**
     * @param Bioquantity $bioquantity
     */
    public function addBioquantity(Bioquantity $bioquantity)
    {
        if ($this->bioquantities->contains($bioquantity)) {
            return;
        }
        $this->bioquantities->add($bioquantity);
        $bioquantity->addExperiment($this);
    }

    /**
     * @param Bioquantity $bioquantity
     */
    public function removeBioquantity(Bioquantity $bioquantity)
    {
        if (!$this->bioquantities->contains($bioquantity)) {
            return;
        }
        $this->bioquantities->removeElement($bioquantity);
        $bioquantity->removeExperiment($this);
    }


    /**
     * @param Device $device
     */
    public function addDevice(Device $device)
    {
        if ($this->devices->contains($device)) {
            return;
        }
        $this->devices->add($device);
        $device->addExperiment($this);
    }

    /**
     * @param Device $device
     */
    public function removeDevice(Device $device)
    {
        if (!$this->devices->contains($device)) {
            return;
        }
        $this->devices->removeElement($device);
        $device->removeExperiment($this);
    }

    /**
     * @param Model $model
     */
    public function addModel(Model $model)
    {
        if ($this->experimentModels->contains($model)) {
            return;
        }
        $this->experimentModels->add($model);
        $model->addExperiment($this);
    }

    /**
     * @param Model $model
     */
    public function removeModel(Model $model)
    {
        if (!$this->experimentModels->contains($model)) {
            return;
        }
        $this->experimentModels->removeElement($model);
        $model->removeExperiment($this);
    }

    /**
     * @param  Experiment $experiment
     * @return void
     */
    public function addExperiment(Experiment $experiment)
    {
        if (!$this->experimentRelation->contains($experiment)) {
            $this->experimentRelation->add($experiment);
            $experiment->addExperiment($experiment);
        }
    }

    /**
     * @param  Experiment $experiment
     * @return void
     */
    public function removeExperiment(Experiment $experiment)
    {
        if ($this->experimentRelation->contains($experiment)) {
            $this->experimentRelation->removeElement($experiment);
            $experiment->removeExperiment($experiment);
        }
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
     * @return Bioquantity[]|Collection
     */
    public function getBioquantities(): Collection
    {
        return $this->bioquantities;
    }

    /**
     * @return Device[]|Collection
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

	/**
	 * @return ExperimentNote[]|Collection
	 */
	public function getNote(): Collection
	{
		return $this->notes;
	}

    /**
     * @return Experiment[]|Collection
     */
    public function getExperimentRelation(): Collection
    {
        return $this->experimentRelation;
    }


    /**
     * @return Model[]|Collection
     */
    public function getExperimentModels(): Collection
    {
        return $this->experimentModels;
    }
}