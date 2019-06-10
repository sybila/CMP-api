<?php

namespace App\Entity;

use App\Entity\AnalysisToolSetting;
use App\Entity\IdentifiedObject;

use App\Entity\SBase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_task")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelTask implements IdentifiedObject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer|null
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer", name="user_id")
     */
    private $userId;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;


    /**
     * @var int
     * @ORM\Column(type="integer", name="model_id")
     */
    protected $modelId;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ModelChange", mappedBy="taskId")
     */
    private $modelChanges;

    /**
     * @var int
     * @ORM\Column(type="integer", name="analysis_tool_id")
     */
    private $analysisToolId;


    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AnalysisToolSetting", mappedBy="taskId")
     */
    private $analysisToolSettings;

    /**
     * @var int
     * @ORM\Column(type="integer", name="is_public")
     */
    protected  $isPublic;

    /**
     * @var int
     * @ORM\Column(type="integer", name="is_postponed")
     */
    protected $isPostponed;


    /**
     * @var string
     * @ORM\Column(type="string", name="output_path")
     */
    private $outputPath;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $annotation;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $notes;

    /**
     * Get id
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string $name
     * @return ModelTask
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

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
     * Set isPostponed
     * @param integer $isPostponed
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
     * @return modelChanges[]|Collection
     */
    public function getModelChanges()
    {
        return $this->modelChanges;
    }


    /**
     * @param mixed $analysisToolId
     * @return ModelTask
     */
    public function setAnalysisToolId($analysisToolId)
    {
        $this->analysisToolId = $analysisToolId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnalysisToolId()
    {
        return $this->analysisToolId;
    }


    /**
     * Get annotation
     * @return string
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }

    /**
     * Set annotation
     * @param string $annotation
     * @return ModelTask
     */
    public function setAnnotation($annotation)
    {
        $this->annotation = $annotation;
        return $this;
    }

    /**
     * Get notes
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set notes
     * @param string $notes
     * @return ModelTask
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @param int $isPublic
     * @return ModelTask
     */
    public function setIsPublic(int $isPublic): ModelTask
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsPublic(): int
    {
        return $this->isPublic;
    }


    /**
     * @return ArrayCollection
     */
    public function getAnalysisToolSettings(): Collection
    {
        return $this->analysisToolSettings;
    }

}