<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="unit")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Unit implements IdentifiedObject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="PhysicalQuantity", inversedBy="units")
     * @ORM\JoinColumn(name="qua_id", referencedColumnName="id")
     */
    protected $quantityId;

    /**
     * @var string
     * @ORM\Column(type="string", name="preferred_name")
     */
    private $preferredName;

    /**
     * @var double
     * @ORM\Column(type="float", name="coefficient")
     */
    private $coefficient;

    /**
     * @var string
     * @ORM\Column(type="string", name="sbml_id")
     */
    private $sbmlId;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UnitAlias", mappedBy="unitId", orphanRemoval=true)
     */
    private $aliases;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Attribute")
     * @ORM\JoinTable(name="unit_attribute_excluded_unit", joinColumns={@ORM\JoinColumn(name="att_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="unit_id", referencedColumnName="id")})
     */
    private $excluded_attributes;

    public function __construct()
    {
        $this->aliases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get name
     */
    public function getQuantityId()
    {
        return $this->quantityId;
    }

    /**
     * Set name
     * @param $quantityId
     * @return Unit
     */
    public function setQuantityId($quantityId): Unit
    {
        $this->quantityId = $quantityId;
        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getPreferredName(): ?string
    {
        return $this->preferredName;
    }

    /**
     * Set name
     * @param string $name
     * @return Unit
     */
    public function setPreferredName($name): Unit
    {
        $this->preferredName = $name;
        return $this;
    }

    /**
     * Get coefficient
     * @return double
     */
    public function getCoefficient(): ?float
    {
        return $this->coefficient;
    }

    /**
     * Set coefficient
     * @param double $coefficient
     * @return Unit
     */
    public function setCoefficient(float $coefficient): Unit
    {
        $this->coefficient = $coefficient;
        return $this;
    }

    /**
     * Get sbml_id
     * @return string
     */
    public function getSbmlId(): ?string
    {
        return $this->sbmlId;
    }

    /**
     * Set sbml_id
     * @param string $sbml_id
     * @return Unit
     */
    public function setSbmlId(string $sbml_id): Unit
    {
        $this->sbmlId = $sbml_id;
        return $this;
    }

    /**
     * @return UnitAlias[]|Collection
     */
    public function getAliases(): Collection
    {
        return $this->aliases;
    }

    /**
     * @param Attribute $excludeAttribute
     */
    public function excludeAttribute(Attribute $excludeAttribute)
    {
        if ($this->excluded_attributes->contains($excludeAttribute)) {
            return;
        }
        $this->excluded_attributes->add($excludeAttribute);
        $excludeAttribute->excludeUnit($this);
    }

    /**
     * @param Attribute $includeAttribute
     */
    public function includeAttribute(Attribute $includeAttribute)
    {
        if ($this->excluded_attributes->contains($includeAttribute)) {
            $this->excluded_attributes->remove($includeAttribute);
        }
        $includeAttribute->includeUnit($this);
    }

    public function addUnitAlias(UnitAlias $unitAlias){
        if($this->aliases->contains($unitAlias)){
            $this->aliases->add($unitAlias);
            $unitAlias->setUnitId($this);
        }
        return $this;
    }
}