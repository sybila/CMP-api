<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="bioquantity_method")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class BioquantityMethod implements IdentifiedObject
{
	use BBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Bioquantity", inversedBy="methods")
	 * @ORM\JoinColumn(name="id_bioquantity", referencedColumnName="id")
	 */
	protected $bioquantityId;


	/**
	 * @var float
	 * @ORM\Column(type="float", name="value")
	 */
	private $value;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="formula")
	 */
	private $formula;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="source")
	 */
	private $source;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="BioquantityVariable", mappedBy="methodId")
	 */
	protected $variables;


	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}
	
	/**
	 * Get bioquantityId
	 * @return integer
	 */
	public function getBioquantityId()
	{
		return $this->bioquantityId;
	}

	/**
	 * Set bioquantityId
	 * @param integer $bioquantityId
	 * @return BioquantityMethod
	 */
	public function setBioquantityId($bioquantityId): BioquantityMethod
	{
		$this->bioquantityId = $bioquantityId;
		return $this;
	}


	/**
	 * Get value
	 * @return float
	 */
	public function getValue(): ?float
	{
		return $this->value;
	}

	/**
	 * Set value
	 * @param string $value
	 * @return BioquantityMethod
	 */
	public function setValue($value): BioquantityMethod
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * Get formula
	 * @return string
	 */
	public function getFormula(): ?string
	{
		return $this->formula;
	}

	/**
	 * Set formula
	 * @param string $formula
	 * @return BioquantityMethod
	 */
	public function setFormula($formula): BioquantityMethod
	{
		$this->formula = $formula;
		return $this;
	}

	/**
	 * Get source
	 * @return string
	 */
	public function getSource(): ?string
	{
		return $this->source;
	}

	/**
	 * Set source
	 * @param string $source
	 * @return BioquantityMethod
	 */
	public function setSource($source): BioquantityMethod
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * @return BioquantityVariable[]|Collection
	 */
	public function getVariables(): Collection
	{
		return $this->variables;
	}
}