<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use EntityAnnotable;

/**
 * Trait SBase
 * @package App\Entity
 * @author Radoslav Doktor & Marek Havlik
 */
trait SBase
{

    use EntityAnnotable;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

	/**
     * This is an alias, an abbreviation, this is used in math expressions
	 * @var string
	 * @ORM\Column(type="string", name="sbml_id")
	 */
	private $alias;

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
	public function getAlias(): ?string
    {
		return $this->alias;
	}

    /**
     * Set sbmlId
     * @param string $alias
     * @return mixed
     */
	public function setAlias(string $alias)
	{
		$this->alias = $alias;
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
     * @return mixed
     */
	public function setName(string $name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get metaId
	 * @return string
	 */
	public function getMetaId(): ?string
    {
		return $this->metaId;
	}

    /**
     * Set metaId
     * @param string $metaId
     * @return mixed
     */
	public function setMetaId(string $metaId)
	{
		$this->metaId = $metaId;
		return $this;
	}

	/**
	 * Get sboTerm
	 * @return string
	 */
	public function getSboTerm(): ?string
    {
		return $this->sboTerm;
	}

    /**
     * Set metaId
     * @param string $sboTerm
     * @return mixed
     */
	public function setSboTerm(string $sboTerm)
	{
		$this->sboTerm = $sboTerm;
		return $this;
	}


	/**
	 * Get notes
	 * @return string
	 */
	public function getNotes(): ?string
    {
		return $this->notes;
	}

    /**
     * Set notes
     * @param string $notes
     * @return mixed
     */
	public function setNotes(string $notes)
	{
		$this->notes = $notes;
		return $this;
	}

}