<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait SBase
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
	 * @ORM\Column(type="string", name="sbml_id")
	 */
	private $sbmlId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="meta_id")
	 */
	private $metaId;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="sbo_term")
	 */
	private $sboTerm;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $annotation;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private $notes;

	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get sbmlId
	 * @return string
	 */
	public function getSbmlId()
	{
		return $this->sbmlId;
	}

	/**
	 * Set sbmlId
	 * @param string $sbmlId
	 * @return Model
	 */
	public function setSbmlId($sbmlId)
	{
		$this->sbmlId = $sbmlId;
		return $this;
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
	 * @return Model
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get metaId
	 * @return string
	 */
	public function getMetaId()
	{
		return $this->name;
	}

	/**
	 * Set metaId
	 * @param string $metaId
	 * @return Model
	 */
	public function setMetaId($metaId)
	{
		$this->metaId = $metaId;
		return $this;
	}

	/**
	 * Get sboTerm
	 * @return string
	 */
	public function getSboTerm()
	{
		return $this->sboTerm;
	}

	/**
	 * Set metaId
	 * @param string $sboTerm
	 * @return Model
	 */
	public function setSboTerm($sboTerm)
	{
		$this->sboTerm = $sboTerm;
		return $this;
	}

	/**
	 * Get annotation
	 * @return string
	 */
	public function getAnnotation()
	{
		return $this->annotation;
	}

	/**
	 * Set annotation
	 * @param string $annotation
	 * @return Model
	 */
	public function setAnnotation($annotation)
	{
		$this->annotation = $annotation;
		return $this;
	}

	/**
	 * Get notes
	 * @return string
	 */
	public function getNotes()
	{
		return $this->annotation;
	}

	/**
	 * Set notes
	 * @param string $notes
	 * @return Model
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;
		return $this;
	}
}