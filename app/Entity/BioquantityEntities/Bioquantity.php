<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\IdentifiedObject;
use Doctrine\Common\Collections\Collection;

/**
 * @author Alexandra Stanová stanovaalex@mail.muni.cz
 * @ORM\Entity
 * @ORM\Table(name="bioquantity")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Bioquantity implements IdentifiedObject
{
	/* TODO: Many to many relations */

use Identifier;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="organism_id")
	 */
	protected $organismId;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="user_id")
	 */
	protected $userId;

	/**
	 * @var string
	 * @ORM\Column (type="string")
	 */
	protected $name;

	/**
	 * @var int
	 * @ORM\Column(name="is_valid",type="integer")
	 */
	protected $isValid;

	/**
	 * @var float
	 * @ORM\Column(type="float")
	 */
	protected $value;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $link;

	/**
	 * @var float
	 * @ORM\Column(name="time_from", type="float")
	 */
	protected $timeFrom;

	/**
	 * @var float
	 * @ORM\Column(name="time_to", type="float")
	 */
	protected $timeTo;

	/**
	 * @var float
	 * @ORM\Column(name="value_from", type="float")
	 */
	protected $valueFrom;

	/**
	 * @var float
	 * @ORM\Column(name="value_to", type="float")
	 */
	protected $valueTo;

	/**
	 * @var float
	 * @ORM\Column(name="value_step", type="float")
	 */
	protected $valueStep;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Attribute", inversedBy="bioquantities")
	 * @ORM\JoinTable(name="unit_attribute", joinColumns={@ORM\JoinColumn(name="bioquantity_id", referencedColumnName="id")},
	 * inverseJoinColumns={@ORM\JoinColumn(name="bioquantity_id", referencedColumnName="id")})
	 */
	protected $attributes;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="ExperimentValues", inversedBy="bioquantities")
	 * @ORM\JoinTable(name="bioquantity_to_variable_value", joinColumns={@ORM\JoinColumn(name="bioquantity_id", referencedColumnName="id")},
	 * inverseJoinColumns={@ORM\JoinColumn(name="variable_value_id", referencedColumnName="id")})
	 */
	protected $variables;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Unit", inversedBy="bioquantities")
	 * @ORM\JoinTable(name="unit", joinColumns={@ORM\JoinColumn(name="bioquantity_id", referencedColumnName="id")},
	 * inverseJoinColumns={@ORM\JoinColumn(name="bioquantity_id", referencedColumnName="id")})
	 */
	protected $unitDefinitions;


	public function getOrganismId(): ?int
	{
		return $this->organismId;
	}


	public function setOrganismId(int $organismId): self
	{
		$this->organismId = $organismId;
		return $this;
	}


	public function getUnitId(): int
	{
		return $this->unitId;
	}


	public function setUnitId(int $unitId): self
	{
		$this->unitId = $unitId;
		return $this;
	}


	public function getUserId(): ?int
	{
		return $this->userId;
	}


	public function setEntityId(int $entityId): self
	{
		$this->entityId = $entityId;
		return $this;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}


	public function getIsValid(): int
	{
		return $this->isValid;
	}


	public function setIsValid(int $isValid): self
	{
		$this->isValid = $isValid;
		return $this;
	}


	public function getValue(): float
	{
		return $this->value;
	}


	public function setValue(float $value): self
	{
		$this->value = $value;
		return $this;
	}


	public function getLink(): ?string
	{
		return $this->link;
	}


	public function setLink(int $link): self
	{
		$this->link = $link;
		return $this;
	}


	public function getTimeFrom(): ?float
	{
		return $this->timeFrom;
	}


	public function setTimeFrom(float $timeFrom): self
	{
		$this->timeFrom = $timeFrom;
		return $this;
	}


	public function getTimeTo(): ?float
	{
		return $this->timeTo;
	}


	public function setTimeTo(float $timeTo): self
	{
		$this->timeTo = $timeTo;
		return $this;
	}


	public function getValueFrom(): ?float
	{
		return $this->valueFrom;
	}


	public function setValueFrom(float $valueFrom): self
	{
		$this->valueFrom = $valueFrom;
		return $this;
	}


	public function getValueTo(): ?float
	{
		return $this->valueTo;
	}


	public function setValueTo(float $valueTo): self
	{
		$this->valueTo = $valueTo;
		return $this;
	}


	public function getValueStep(): ?float
	{
		return $this->valueStep;
	}


	public function setValueStep(float $valueStep): self
	{
		$this->valueStep = $valueStep;
		return $this;
	}

    /**
     * @return Attributes[]|Collection
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    /**
     * @return Variables[]|Collection
     */
    public function getVariables(): Collection
    {
        return $this->variables;
    }

    /**
     * @return UnitDefinitions[]|Collection
     */
    public function getUnitDefinitions(): Collection
    {
        return $this->unitDefinitions;
    }

}
