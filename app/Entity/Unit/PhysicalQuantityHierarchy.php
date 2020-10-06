<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="unit_physical_quantity_hierarchy")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class PhysicalQuantityHierarchy implements IdentifiedObject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="qua_id")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="PhysicalQuantity", inversedBy="hierarchy")
     * @ORM\JoinColumn(name="qua_id", referencedColumnName="id")
     */
    private $quantityId;

    /**
     * @var string
     * @ORM\Column(type="string", name="function")
     */
    private $function;

    /**
     * Get function
     * @return string
     */
    public function getFunction(): ?string
    {
        return $this->function;
    }

    /**
     * Set function
     * @param string $function
     * @return PhysicalQuantityHierarchy
     */
    public function setFunction(string $function): PhysicalQuantityHierarchy
    {
        $this->function = $function;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get quantityId
     * @return int
     */
    public function getQuantityId(): ?int
    {
        return $this->quantityId;
    }

    /**
     * Set quantityId
     * @param integer $quantityId
     * @return PhysicalQuantityHierarchy
     */
    public function setQuantityId(int $quantityId): PhysicalQuantityHierarchy
    {
        $this->quantityId = $quantityId;
        return $this;
    }
}