<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait BBase
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
	//private $name;

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
	/*public function getName()
	{
		return $this->name;
	}*/

	/**
	 * Set name
	 * @param string $name
	 * @return Experiment
	 */
	/*public function setName($name)
	{
		$this->name = $name;
		return $this;
	}*/
}