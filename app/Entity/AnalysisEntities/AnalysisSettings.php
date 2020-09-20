<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="analysis_settings")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class AnalysisSettings implements IdentifiedObject
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
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var int
     * @ORM\Column(type="integer", name="method_id")
     */
    private $methodId;


    /**
     * @var array stores the names, boundaries and default values in JSON format.
     * @ORM\Column(type="json", name="method_settings"  )
     */
    private $methodSettings;

    /**
     * @return int
     */
    public function getMethodId(): int
    {
        return $this->methodId;
    }

    /**
     * @param int $methodId
     */
    public function setMethodId(int $methodId): void
    {
        $this->methodId = $methodId;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getMethodSettings(): array
    {
        return $this->methodSettings;
    }

    /**
     * @param string $methodSettings
     */
    public function setMethodSettings(string $methodSettings): void
    {
        $this->methodSettings = $methodSettings;
    }


}