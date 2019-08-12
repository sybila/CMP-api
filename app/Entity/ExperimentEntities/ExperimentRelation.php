<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_to_experiment")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentRelation implements IdentifiedObject
{
	use EBase;

    /**
     * @ORM\ManyToOne(targetEntity="Experiment", inversedBy="experimentRelation", fetch="EAGER")
     * @ORM\JoinColumn(name="1exp_id", referencedColumnName="id")
     */
	protected $firstExperimentId;

	/**
	 * @ORM\ManyToOne(targetEntity="Experiment", inversedBy="experimentRelation", fetch="EAGER")
	 * @ORM\JoinColumn(name="2exp_id", referencedColumnName="id")
	 */
	protected $secondExperimentId;


    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Experiment", mappedBy="firstExperimentId")
     */
    protected $relatedExperiments;

	/**
	 * Get firstExperimentId
	 * @return Experiment|null
	 */
	public function getFirstExperimentId(): ?Experiment
	{
		return $this->firstExperimentId;
	}

	/**
	 * Set firstExperimentId
	 * @param int $firstExperimentId
	 * @return ExperimentRelation|null
	 */
	public function setFirstExperimentId($firstExperimentId): ExperimentRelation
	{
		$this->firstExperimentId = $firstExperimentId;
		return $this;
	}

	/**
	 * Get secondExperimentId
	 * @return Experiment|null
	 */
	public function getSecondExperimentId(): ?Experiment
	{
		return $this->secondExperimentId;
	}

	/**
	 * Set secondExperimentId
	 * @param int $secondExperimentId
	 * @return ExperimentRelation
	 */
	public function setSecondExperimentId($secondExperimentId): ExperimentRelation
	{
		$this->secondExperimentId = $secondExperimentId;
		return $this;
	}


	/**
	 * @return Experiment[]|Collection
	 */
	public function getRelatedExperiment(): Experiment
	{
		return $this->relatedExperiments;
	}
}