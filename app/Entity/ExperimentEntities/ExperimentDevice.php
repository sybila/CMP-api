<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_device")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentDevice implements IdentifiedObject
{
	use EBase;


	/**
	 * @ORM\ManyToMany(targetEntity="Experiment", inversedBy="devices")
	 * @ORM\JoinColumn(name="exp_id", referencedColumnName="id")
	 */
	protected $experimentId;

	/**
	 * @ORM\ManyToMany(targetEntity="Device", inversedBy="experiments")
	 * @ORM\JoinColumn(name="dev_id", referencedColumnName="id")
	 */
	protected $deviceId;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Experiment", mappedBy="experimentId")
     */
    private $experiments;

	/**
	 * Get experimentId
	 * @return int
	 */
	public function getExperimentId(): ?int
	{
		return $this->experimentId;
	}

	/**
	 * Set experimentId
	 * @param int $experimentId
	 * @return ExperimentDevice
	 */
	public function setExperimentId($experimentId): ExperimentDevice
	{
		$this->experimentId = $experimentId;
		return $this;
	}

	/**
	 * Get deviceId
	 * @return int
	 */
	public function getDeviceId(): ?int
	{
		return $this->deviceId;
	}

	/**
	 * Set deviceId
	 * @param int $deviceId
	 * @return ExperimentDevice
	 */
	public function setDeviceId($deviceId): ExperimentDevice
	{
		$this->deviceId = $deviceId;
		return $this;
	}

    /**
     * @return Experiment[]|Collection
     */
    public function getExperiments(): Collection
    {
        return $this->experiments;
    }

}
