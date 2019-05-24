<?php


use App\Entity\AnalysisToolSetting;
use App\Entity\IdentifiedObject;

use App\Entity\SBase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Task
 * @ORM\Table(name="model_task")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelTask implements IdentifiedObject
{
    use SBase;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @var int
     * @ORM\Column(type="integer", name="user_id")
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     */
    protected $modelId;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="modelChange", mappedBy="modelId")
     */
    private $modelChanges;

    /**
     * @ORM\OneToOne(targetEntity="AnalysisTool")
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id")
     */
    protected $analysisToolId;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="taskConfig", mappedBy="modelId")
     */
    private $taskConfigs;


    /**
     * @var int
     * @ORM\Column(type="integer", name="is_postponed")
     */
    protected $isPostponed;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $outputPath;

    /**
     * Get modelId
     * @return integer
     */
    public function getModelId(): ?int
    {
        return $this->modelId;
    }

    /**
     * Set modelId
     * @param integer $modelId
     * @return ModelTask
     */
    public function setModelId($modelId): ModelTask
    {
        $this->modelId = $modelId;
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
     * @return ModelTask
     */
    public function setUserId($userId): ModelTask
    {
        $this->userId = $userId;
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
     * @return ModelTask
     */
    public function setDescription($description): ModelTask
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get isReversible
     * @return integer
     */
    public function getIsReversible()
    {
        return $this->isPostponed;
    }

    /**
     * Set isPostponed
     * @param integer $isReversible
     * @return ModelTask
     */
    public function setIsPostponed($isPostponed): ModelTask
    {
        $this->isPostponed = $isPostponed;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsPostponed(): int
    {
        return $this->isPostponed;
    }


    /**
     * @param string $outputPath
     * @return ModelTask
     */
    public function setOutputPath(string $outputPath): ModelTask
    {
        $this->outputPath = $outputPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    /**
     * @return AnalysisToolSetting[]|Collection
     */
    public function getModelChanges()
    {
        return $this->modelChanges;
    }


    /**
     * @return AnalysisToolSetting[]|Collection
     */
    public function getAnalysisToolSettings(): Collection
    {
        return $this->analysisToolSettings;
    }

}