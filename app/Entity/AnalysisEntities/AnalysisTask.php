<?php

namespace App\Entity;

use App\Entity\IdentifiedObject;

use App\Entity\SBase;
use App\Exceptions\InvalidEnumValueException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="analysis_task")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class AnalysisTask implements IdentifiedObject
{
    const OBJ_TYPES = ['model', 'experiment'];

    use AnalysisBase;


    /**
     * @var int
     * @ORM\Column(type="integer", name="user_id")
     */
    private $userId;


    /**
     * @var int
     * @ORM\Column(type="integer", name="object_id")
     */
    private $objectId;

    /**
     * @var string currently model or experiment
     * @ORM\Column (type="string", name="object_type", columnDefinition="ENUM('model', 'experiment')")
     */
    private $objectType;

    /**
     * @var int
     * @ORM\OneToOne(targetEntity="AnalysisDataset")
     * @ORM\JoinColumn(name="dataset_id", referencedColumnName="id")
     */
    private $dataset;

    /**
     * @var int
     * @ORM\OneToOne(targetEntity="AnalysisSettings")
     * @ORM\JoinColumn(name="settings_id", referencedColumnName="id")
     */
    private $settings;

    /**
     * @var object
     * @ORM\OneToOne(targetEntity="AnalysisMethod")
     * @ORM\JoinColumn(name="method_id", referencedColumnName="id")
     */
    private $method;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", name="is_public")
     */
    private  $isPublic;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", name="is_postponed")
     */
    private $isPostponed;


    /**
     * @var string
     * @ORM\Column(type="string", name="output_path")
     */
    //private $outputPath;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $notes;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getObjectId(): int
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     */
    public function setObjectId(int $objectId): void
    {
        $this->objectId = $objectId;
    }

    /**
     * @return string
     */
    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     * @throws InvalidEnumValueException
     */
    public function setObjectType(string $objectType): void
    {
        if (!in_array($objectType, self::OBJ_TYPES))
            throw new InvalidEnumValueException("objectType", $objectType, self::OBJ_TYPES);
        $this->objectType = $objectType;
    }

    /**
     * return object
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    /**
     * @param int $datasetId
     */
    public function setDataset(int $datasetId): void
    {
        $this->dataset = $datasetId;
    }

    /**
     * return object
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param int $settingsId
     */
    public function setSettings(int $settingsId): void
    {
        $this->settings = $settingsId;
    }

    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param int $methodId
     */
    public function setMethod(int $methodId): void
    {
        $this->method = $methodId;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic
     */
    public function setIsPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }

    /**
     * @return bool
     */
    public function isPostponed(): bool
    {
        return $this->isPostponed;
    }

    /**
     * @param bool $isPostponed
     */
    public function setIsPostponed(bool $isPostponed): void
    {
        $this->isPostponed = $isPostponed;
    }

    /**
     * @return string
     */
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    /**
     * @param string $outputPath
     */
    public function setOutputPath(string $outputPath): void
    {
        $this->outputPath = $outputPath;
    }

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }




}