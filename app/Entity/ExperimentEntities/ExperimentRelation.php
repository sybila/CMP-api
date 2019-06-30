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
	 * @ORM\ManyToOne(targetEntity="ExperimentRelation", inversedBy="firstExperimentId")
	 * @ORM\JoinColumn(name="1exp_id", referencedColumnName="id")
	 */
	protected $firstExperimentId;

	/**
	 * @ORM\ManyToOne(targetEntity="ExperimentRelation", inversedBy="secondExperimentId")
	 * @ORM\JoinColumn(name="2exp_id", referencedColumnName="id")
	 */
	protected $secondExperimentId;

	/**
	 * Get firstExperimentId
	 * @return int
	 */
	public function getFirstExperimentId(): ?int
	{
		return $this->firstExperimentId;
	}

	/**
	 * Set firstExperimentId
	 * @param int $firstExperimentId
	 * @return ExperimentRelation
	 */
	public function setFirstExperimentId($firstExperimentId): ExperimentRelation
	{
		$this->firstExperimentId = $firstExperimentId;
		return $this;
	}

	/**
	 * Get secondExperimentId
	 * @return int
	 */
	public function getSecondExperimentId(): ?int
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
	public function getExpertFirst(): Collection
	{
		return $this->firstExperiment;
	}

	/**
	 * @return Experiment[]|Collection
	 */
	public function getExpertSecond(): Collection
	{
		return $this->secondExperiment;
	}
}