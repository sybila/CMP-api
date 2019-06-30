<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_device")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Device implements IdentifiedObject
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
	 * @return ExperimentDevices
	 */
	public function setExperimentId($experimentId): ExperimentDevices
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
	 * @return ExperimentDevices
	 */
	public function setDeviceId($deviceId): ExperimentDevices
	{
		$this->deviceId = $deviceId;
		return $this;
	}

}
