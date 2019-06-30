<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="bioquantities")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Bioquantity implements IdentifiedObject
{
	//use SBase;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="name")
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="description")
	 */
	private $description;

	/**
	 * @ORM\ManyToOne(targetEntity="Organism", inversedBy="experiments")
	 * @ORM\JoinColumn(name="organism_id", referencedColumnName="id")
	 */
	private $organismId;

	/**Unit neexistuje
	 * @ORM\ManyToOne(targetEntity="...", inversedBy="...")
	 * @ORM\JoinColumn(name="unit_id", referencedColumnName="id")
	 */
	private $unitId;

	/**User neexistuje
	 * @ORM\ManyToOne(targetEntity="...", inversedBy="...")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	private $userId;

	/**
	 * @var bool
	 * @ORM\Column(type="bool", name="is_valid")
	 */
	private $IsValid;

	/**
	 * @var bool
	 * @ORM\Column(type="bool", name="is_automatic")
	 */
	private $IsAutomatic;

	/**
	 * @ORM\ManyToOne(targetEntity="Entity", inversedBy="bioquantities")
	 * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
	 */
	private $entityId;

	/**Atribut neexistuje
	 * @ORM\ManyToOne(targetEntity="...", inversedBy="...")
	 * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
	 */
	private $attributeId;

	/**
	 * @var ArrayCollection//Bioquantities with experiments
	 * @ORM\OneToMany(targetEntity="...", mappedBy="...")
	 */
	private $experimentsId;


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
	 * @return Bioquantity
	 */
	public function setName($name): Bioquantity
	{
		$this->name = $name;
		return $this;
	}

		/**
	 * Get description
	 * @return string
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * Set description
	 * @param string $description
	 * @return Bioquantity
	 */
	public function setDescription($description): Bioquantity
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Get organismId
	 * @return int
	 */
	public function getOrganismId(): ?int
	{
		return $this->organismId;
	}

	/**
	 * Set organismId
	 * @param int $organismId
	 * @return Bioquantity
	 */
	public function setOrganismId($organismId): Bioquantity
	{
		$this->organismId = $organismId;
		return $this;
	}

	/**
	 * Get unitId
	 * @return int
	 */
	public function getUnitId(): ?int
	{
		return $this->unitId;
	}

	/**
	 * Set unitId
	 * @param int $unitId
	 * @return Bioquantity
	 */
	public function setUnitId($unitId): Bioquantity
	{
		$this->unitId = $unitId;
		return $this;
	}

	/**
	 * Get userId
	 * @return integer
	 */
	public function getUserId(): ?int
	{
		return $this->userId;
	}

	/**
	 * Set userId
	 * @param int $userId
	 * @return Bioquantity
	 */
	public function setUserId($userId): Bioquantity
	{
		$this->userId = $userId;
		return $this;
	}

	/**
	 * Get isAutomatic
	 * @return bool
	 */
	public function getIsAutomatic(): ?bool
	{
		return $this->isAutomatic;
	}

	/**
	 * Set isAutomatic
	 * @param bool $isAutomatic
	 * @return Bioquantity
	 */
	public function setIsAutomatic($isAutomatic): Bioquantity
	{
		$this->isAutomatic = $isAutomatic;
		return $this;
	}

}