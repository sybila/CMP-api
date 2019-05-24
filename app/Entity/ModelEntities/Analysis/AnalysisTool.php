<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="analysis_tool")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class AnalysisTool implements IdentifiedObject
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
     * @var string
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="AnalysisToolSetting", mappedBy="procId", cascade={"persist"})
     */
    protected $analysisToolSettings;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $cmd;


    /**
     * @var int
     * @ORM\Column(type="integer", name="viz_id")
     */
    protected $vizId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $location;


    /**
     * @return AnalysisToolSetting[]|Collection
     */
    /*public function getAnalysisToolSettings(): Collection
    {
        return $this->analysisToolSettings;
    }*/

    /**
     * Set description
     * @param string $description
     * @return AnalysisTool
     */
    public function setDescription(string $description): AnalysisTool
    {
        $this->description = $description;
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
     * Set name
     * @param string $name
     * @return AnalysisTool
     */
    public function setName(string $name): AnalysisTool
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get id
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $cmd
     * @return AnalysisTool
     */
    public function setCmd(string $cmd): AnalysisTool
    {
        $this->cmd = $cmd;
        return $this;
    }

    /**
     * @return string
     */
    public function getCmd(): string
    {
        return $this->cmd;
    }

    /**
     * @param int $vizId
     * @return AnalysisTool
     */
    public function setVizId(int $vizId): AnalysisTool
    {
        $this->vizId = $vizId;
        return $this;
    }

    /**
     * @return int
     */
    public function getVizId(): int
    {
        return $this->vizId;
    }

    /**
     * @param string $location
     * @return AnalysisTool
     */
    public function setLocation(string $location): AnalysisTool
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

}