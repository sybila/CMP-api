<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

interface IdentifiedObject
{
	public function getId(): ?int;
}

trait Identifier
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

	final public function getId(): ?int
	{
		return $this->id;
	}

	public function __clone()
	{
		$this->id = NULL;
	}
}
