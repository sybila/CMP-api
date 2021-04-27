<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Entity\Model;
use App\Entity\ModelCompartment;
use App\Entity\ModelDataset;
use App\Entity\ModelVarToDataset;
use App\Entity\Repositories\ModelDatasetRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

class ModelDatasetController extends ParentedRepositoryController
{

    protected static function getAllowedSort(): array
    {
        // TODO: Implement getAllowedSort() method.
    }

    protected function getParentObjectInfo(): ParentObjectInfo
    {
        return new ParentObjectInfo('model-id', Model::class);
    }

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelDataset $child */
        /** @var Model $parent */
        if ($parent !== $child->getModel()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }

    protected static function getRepositoryClassName(): string
    {
        return ModelDatasetRepository::class;
    }

    protected static function getObjectName(): string
    {
        return ModelDataset::class;
    }

    protected function getData(IdentifiedObject $object): array
    {
        /** @var ModelDataset $object */
        $vars = ['compartments'=> [], 'species' => [], 'parameters' => []];
        $object->getVarsToDataset()->map(function (ModelVarToDataset $var) use (&$vars) {
            switch ($var->getVarType()) {
                case 'compartment':
                    /** @var ModelCompartment $cpt */
                    $cpt = $var->getCompartment();
                    array_push($vars['compartments'], [
                        'id' => $cpt->getId(),
                        'alias' => $cpt->getAlias(),
                        'initialValue' => $var->getValue()
                        ]);
                    break;
                case 'species':
                    $spec = $var->getSpecies();
                    array_push($vars['species'], [
                        'id' => $spec->getId(),
                        'alias' => $spec->getAlias(),
                        'initialValue' => $var->getValue()
                    ]);
                    break;
                case 'parameter':
                    $par = $var->getParameter();
                    array_push($vars['parameters'], [
                        'id' => $par->getId(),
                        'alias' => $par->getAlias(),
                        'initialValue' => $var->getValue()
                    ]);
                    break;
            }
        });
        return [
            "id" => $object->getId(),
            "name" => $object->getName(),
            "default" => $object->getIsDefault(),
            "initialValues" => $vars
        ];
    }

    protected function setData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var ModelDataset $object */
        !$body->hasKey('name') ?: $object->setName($body->getString('name'));
        if ($body->hasKey('initialValues')) {
            foreach ($body->getArray('initialValues') as $datasetVariable) {
                if (array_key_exists('alias', $datasetVariable) &&
                    array_key_exists('initialValue', $datasetVariable)) {
                    foreach ($object->getVarsToDataset() as &$var) {
                        $var->setDataset($object);
                        if ($var->getModelVar()->getAlias() === $datasetVariable['alias']) {
                            $var->setValue($datasetVariable['initialValue']);
                        }
                    }
                }
            }
        }
        !$body->hasKey('default') ?: $object->setTheDefaultDataset($body->get('default'));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        /** @var Model $model */
        $model = $this->repository->getParent();
        if (!$body->hasKey('name')) {
            throw new MissingRequiredKeyException('name');
        }
        $ds = new ModelDataset($model, $body->getString('name'),false);
        $ds->setVarsToDataset($model->getDefaultDataset()->getVarsToDataset()->map(function (ModelVarToDataset $var){
            return (clone $var);
        }));
        return $ds;
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        /** @var ModelDataset $object */
        if ($object->getName() == '')
            throw new MissingRequiredKeyException('name');
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Type(['type' => 'string']),
            'initialValues' => new Assert\Type(['type' => 'array']),
//            'description' => new Assert\Type(['type' => 'string']),
//            'visualisation' => new Assert\Type(['type' => 'string']),
//            'status' => new Assert\Type(['type' => 'string']),
        ]);
    }
}