<?php

namespace App\Entity;

use App\Helpers\DateTimeJson;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="unit_attribute")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Attribute implements IdentifiedObject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="PhysicalQuantity", inversedBy="attributes")
     * @ORM\JoinColumn(name="qua_id", referencedColumnName="id")
     */
    protected $quantityId;

    /**
     * @var string
     * @ORM\Column(type="string", name="name")
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", name="note")
     */
    private $note;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Unit")
     * @ORM\JoinTable(name="unit_attribute_excluded_unit", joinColumns={@ORM\JoinColumn(name="att_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="unit_id", referencedColumnName="id")})
     */
    private $excluded_units;

    public function __construct()
    {
        $this->excluded_units = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get quantityId
     */
    public function getQuantityId()
    {
        return $this->quantityId;
    }

    /**
     * Set quantityId
     * @param integer $quantityId
     * @return Attribute
     */
    public function setQuantityId($quantityId): Attribute
    {
        $this->quantityId = $quantityId;
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
     * @return Attribute
     */
    public function setName($name): Attribute
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get note
     * @return string
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * Set note
     * @param string $note
     * @return Attribute
     */
    public function setNote($note): Attribute
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return Unit[]|Collection
     */
    public function getExcludedUnits(): Collection
    {
        return $this->excluded_units;
    }

    /**
     * @param Unit $excludeUnit
     */
    public function excludeUnit(Unit $excludeUnit)
    {
        if ($this->excluded_units->contains($excludeUnit)) {
            return;
        }
        $this->excluded_units->add($excludeUnit);

    }

    /**
     * @param Unit $includeUnit
     */
    public function includeUnit(Unit $includeUnit)
    {
        if ($this->excluded_units->contains($includeUnit)) {
            $this->excluded_units->remove($includeUnit);
        }
    }
}