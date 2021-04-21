<?php


namespace App\Entity;

use App\Exceptions\ActionConflictException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="model_dataset")
 */
class ModelDataset implements IdentifiedObject
{
    use Identifier;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ModelVarToDataset", mappedBy="dataset", cascade={"persist", "remove"})
     */
    private $varsToDataset;

    /**
     * @var Model $model
     * @ORM\ManyToOne(targetEntity="Model", inversedBy="datasets")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     */
    private $model;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="is_default")
     */
    private $isDefault;


    /**
     * ModelDataset constructor.
     * @param Model $model
     * @param $name
     * @param $isDefault
     */
    public function __construct(Model $model, $name, $isDefault)
    {
        $this->model = $model;
        $this->name = $name;
        $this->isDefault = $isDefault;
        $this->varsToDataset = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getVarsToDataset()
    {
        return $this->varsToDataset;
    }

    /**
     * @param mixed $varsToDataset
     */
    public function setVarsToDataset($varsToDataset): void
    {
        $this->varsToDataset = $varsToDataset;
    }

    public function addVarToDataset($var)
    {
        $this->varsToDataset->add($var);
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param Model $model
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param mixed $isDefault
     */
    public function setIsDefault($isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    /**
     * @param mixed $isDefault
     * @throws ActionConflictException
     */
    public function setTheDefaultDataset($isDefault): void
    {
        if (!$isDefault) {
            if ($this->getIsDefault()) {
                throw new ActionConflictException("Model has to have a default dataset.");
            }
        } else {
            $this->model->getDatasets()->map(function (ModelDataset &$ds) {
                $ds->setIsDefault(false);
            });
            $this->isDefault = $isDefault;
        }
    }

    public function getDatasetVariableValue(string $type, int $id, float &$result): ?float
    {
        foreach ($this->varsToDataset as $varChange) {
            /** @var ModelVarToDataset $varChange */
            if ($type === $varChange->getVarType()) {
                switch ($type) {
                    case 'parameter': {
                        if ($varChange->getParameter()->getId() == $id){
                            $result = $varChange->getValue();
                            return true;
                        }
                    }
                    case 'species': {
                        if ($varChange->getSpecies()->getId() == $id) {
                            $result = $varChange->getValue();
                            return true;
                        }
                    }
                    case 'compartment': {
                        if ($varChange->getCompartment()->getId() == $id) {
                            $result = $varChange->getValue();
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }



}