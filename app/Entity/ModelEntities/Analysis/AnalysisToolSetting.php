<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class AnalysisToolSetting
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
     * @ORM\ManyToOne(targetEntity="AnalysisTool", inversedBy="analysisToolSettings")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    protected $taskId;

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
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $value;

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
     * @return AnalysisToolSetting
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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
     * @return AnalysisToolSetting
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
     * @return AnalysisToolSetting
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Get value
     * @return integer
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * Set value
     * @param integer $value
     * @return AnalysisToolSetting
     */
    public function setValue($value): AnalysisToolSetting
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get taskId
     * @return integer
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * Set taskId
     * @param integer $taskId
     * @return AnalysisToolSetting
     */
    public function setTaskId($taskId): AnalysisToolSetting
    {
        $this->taskId = $taskId;
        return $this;
    }


}