<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="devices")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Device implements IdentifiedObject
{
	use EBase;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="type")
	 */
	private $type;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="name")
	 */
	private $name;

	/**
	 * @var integer
	 * @ORM\Column(type="integer", name="address")
	 */
	private $address;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="ExperimentDevices", mappedBy="deviceId")
	 */
	private $experiments;

	/**
	 * Get type
	 * @return string
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * Set type
	 * @param string $type
	 * @return Device
	 */
	public function setType($type): Device
	{
		$this->type = $type;
		return $this;
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
	 * @return Device
	 */
	public function setName($name): Device
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get address
	 * @return int
	 */
	public function getAddress(): ?int
	{
		return $this->address;
	}

	/**
	 * Set address
	 * @param int $address
	 * @return Device
	 */
	public function setAddress($address): Device
	{
		$this->address = $address;
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
