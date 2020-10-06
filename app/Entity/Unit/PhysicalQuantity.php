<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="unit_physical_quantity")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class PhysicalQuantity implements IdentifiedObject
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
     * @ORM\Column(type="string", name="name")
     */
    private $name;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Unit", mappedBy="quantityId", orphanRemoval=true)
     */
    private $units;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Attribute", mappedBy="quantityId", orphanRemoval=true)
     */
    private $attributes;

    /**
     * @var PhysicalQuantityHierarchy
     * @ORM\OneToOne(targetEntity="PhysicalQuantityHierarchy", mappedBy="quantityId", orphanRemoval=true)
     */
    private $hierarchy;

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
     * @return PhysicalQuantity
     */
    public function setName($name): PhysicalQuantity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Unit[]|Collection
     */
    public function getUnits(): Collection
    {
        return $this->units;
    }

    /**
     * @return Attribute[]|Collection
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    /**
     * Get id
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHierarchy(): ?PhysicalQuantityHierarchy
    {
        return $this->hierarchy;
    }
}