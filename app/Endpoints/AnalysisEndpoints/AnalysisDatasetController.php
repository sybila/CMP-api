<?php


namespace App\Controllers;


use App\Entity\AnalysisDataset;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnalysisDatasetRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

class AnalysisDatasetController extends ParentedRepositoryController
{

    protected static function getRepositoryClassName(): string
    {
        return AnalysisDatasetRepository::class;
    }

    protected static function getObjectName(): string
    {
        return 'analysisDataset';
    }

    protected function getData(IdentifiedObject $object): array
    {
        /** @var AnalysisDataset $object */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'description' => $object->getDescription(),
            'modelId' => $object->getModelId(),
            'modelDataset' => $object->getDatasetSettings(),
            'annotation' => $object->getAnnotation()
        ];
    }

    protected static function getAllowedSort(): array
    {
        return ['id', 'name', 'modelId'];
    }

    protected static function getAlias(): string
    {
        return 'd';
    }

    protected function setData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var AnalysisDataset $object */
        !$body->hasKey('name') ?: $object->setName($body->getString('name'));
        !$body->hasKey('description') ?: $object->setDescription($body->getString('description'));
        !$body->hasKey('annotation') ?: $object->setAnnotation($body->getString('annotation'));
        !$body->hasKey('datasetSettings') ?: $object->setDatasetSettings($body->getString('datasetSettings'));
        $object->getModelId() ?: $object->setModelId($this->repository->getParent()->getId());
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        $this->verifyMandatoryArguments(['name','datasetSettings'], $body);
        return new AnalysisDataset;
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        /** @var AnalysisDataset $object */
        if ($object->getName() == '')
            throw new MissingRequiredKeyException('name');
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Type(['type' => 'string']),
            'datasetSettings' => new Assert\Json(),
            'description' => new Assert\Type(['type' => 'string']),
            'annotation' => new Assert\Type(['type' => 'string']),
        ]);
    }

    protected function checkParentValidity(IdentifiedObject $model, IdentifiedObject $child)
    {
        /** @var AnalysisDataset $child */
        if ($model->getId() != $child->getModelId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityName, $model->getId(),
                self::getObjectName(), $child->getId());
        }
    }

    protected function getParentObjectInfo(): ParentObjectInfo
    {
        return new ParentObjectInfo('model-id', 'model');
    }
}