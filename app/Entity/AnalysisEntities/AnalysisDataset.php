<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="analysis_dataset")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class AnalysisDataset implements IdentifiedObject
{
    use AnalysisBase;

    /**
     * @var array stores the names, boundaries and default values in JSON format.
     * @ORM\Column(type="json", name="dataset_settings")
     */
    private $datasetSettings;

    /**
     * @ORM\Column(type="integer", name="model_id")
     */
    private $modelId;

    /**
     * @return array
     */
    public function getDatasetSettings()
    {
        return $this->datasetSettings;
    }

    /**
     * @param string $datasetSettings
     */
    public function setDatasetSettings(string $datasetSettings): void
    {
        $this->datasetSettings = $datasetSettings;
    }

    /**
     * @return null|int
     */
    public function getModelId(): ?int
    {
        return $this->modelId;
    }

    /**
     * @param int $modelId
     */
    public function setModelId(int $modelId): void
    {
        $this->modelId = $modelId;
    }




}