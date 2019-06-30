<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EBase
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="description")
	 */
	private $description;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="protocol")
	 */
	private $protocol;

	/**
	 * @var string
	 * @ORM\Column(type="datetime", name="started")
	 */
	private $started;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="status")
	 */
	private $status;

	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
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
	 * @return Experiment
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get description
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set description
	 * @param string $description
	 * @return Experiment
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Get protocol
	 * @return string
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}

	/**
	 * Set protocol
	 * @param string $protocol
	 * @return Experiment
	 */
	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;
		return $this;
	}

	/**
	 * Get started
	 * @return string
	 */
	public function getStarted()
	{
		return $this->started;
	}

	/**
	 * Set started
	 * @param string $started
	 * @return Experiment
	 */
	public function setStarted($started)
	{
		$this->started = $started;
		return $this;
	}

	/**
	 * Get status
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Set status
	 * @param string $status
	 * @return Experiment
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}
}