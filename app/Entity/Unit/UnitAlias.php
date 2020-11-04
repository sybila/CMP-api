<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="unit_alias_name")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class UnitAlias implements IdentifiedObject
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Unit", inversedBy="aliases")
	 * @ORM\JoinColumn(name="unit_id", referencedColumnName="id")
	 */
	protected $unitId;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="alternative_name")
	 */
	private $alternativeName;



	/**
	 * Get name
	 * @return string
	 */
	public function getAlternativeName(): string
	{
		return $this->alternativeName;
	}

	/**
	 * Set name
	 * @param string $alternativeName
	 * @return UnitAlias
	 */
	public function setAlternativeName(string $alternativeName): UnitAlias
	{
		$this->alternativeName = $alternativeName;
		return $this;
	}

    /**
     * Get unit
     * @return string
     */
    public function getUnitId(): ?string
    {
        return $this->unitId;
    }

    /**
     * Set unit
     * @param $unitId
     * @return UnitAlias
     */
    public function setUnitId($unitId): UnitAlias
    {
        $this->unitId = $unitId;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}