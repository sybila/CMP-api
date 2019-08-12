<?php

namespace App\Entity;

use App\Helpers\DateTimeJson;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_to_model")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentModels implements IdentifiedObject
{
	use EBase;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Experiment", fetch="EAGER")
	 * @ORM\JoinColumn(name="experimentId", referencedColumnName="id")
	 */
	protected $ExperimentId;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="...", fetch="EAGER")
	 * @ORM\JoinColumn(name="modelId", referencedColumnName="id")
	 */
	protected $ModelId;

    /**
     * @var DateTimeJson
     * @ORM\Column(type="datetime", name="validated")
     */
    private $validated;

    /**
     * @var int
     * @ORM\Column(type="integer", name="validationUserId")
     */
    private $userId;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Model", mappedBy="ModelId")
     */
    protected $relatedModels;


    /**
     * Get id
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

	/**
	 * Get ExperimentId
	 * @return Experiment
	 */
	public function getExperimentRelationModelId(): Experiment
	{
		return $this->ExperimentId;
	}

	/**
	 * Set ExperimentId
	 * @param int $ExperimentId
	 * @return ExperimentModels
	 */
	public function setExperimentRelationModelId($ExperimentId): ExperimentModels
	{
		$this->ExperimentId = $ExperimentId;
		return $this;
	}

    /**
     * Get validated
     * @return DateTimeJson|null
     */
    public function getValidated(): ?DateTimeJson
    {
        return $this->validated;
    }

    /**
     * Set validated
     * @param DateTimeJson $validated
     * @return ExperimentModels
     */
    public function setValidated($validated): ExperimentModels
    {
        $this->validated = $validated;
        return $this;
    }

    /**
     * Get userId
     * @return integer|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Set userId
     * @param int $userId
     * @return ExperimentModels
     */
    public function setUserId($userId): ExperimentModels
    {
        $this->userId = $userId;
        return $this;
    }

	/**
	 * Get ModelId
	 * @return Model
	 */
	public function getModelRelationExperimentId(): Model
	{
		return $this->ModelId;
	}

	/**
	 * Set ModelId
	 * @param int $ModelId
	 * @return ExperimentRelation
	 */
	public function setModelRelationExperimentId($ModelId): ExperimentModels
	{
		$this->ModelId = $ModelId;
		return $this;
	}


	/**
	 * @return ExperimentModels[]|Collection
	 */
	public function getRelatedModels(): ExperimentModels
	{
		return $this->relatedModels;
	}
}