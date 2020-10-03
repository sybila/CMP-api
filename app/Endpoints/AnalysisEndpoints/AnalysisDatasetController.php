<?php


namespace App\Controllers;


use AnalysisControllerCommonable;
use App\Entity\AnalysisDataset;
use App\Entity\IdentifiedObject;
use App\Entity\Model;
use App\Entity\Repositories\AnalysisDatasetRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

class AnalysisDatasetController extends ParentedRepositoryController
{
    use AnalysisControllerCommonable;

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
        return array_merge($this->getCommonAnalysisData($object), [
            'modelId' => $object->getModelId(),
            'modelDataset' => $object->getDatasetSettings()
        ]);
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
        $this->setCommonAnalysisData($object, $body);
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
        return new Assert\Collection( array_merge(
            $this->getCommonAnalysisDataValidator(), [
            'datasetSettings' => new Assert\Json()
            ]));
    }

    protected function checkParentValidity(IdentifiedObject $model, IdentifiedObject $child)
    {
        /** @var AnalysisDataset $child */
        if ($model->getId() != $child->getModelId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $model->getId(),
                self::getObjectName(), $child->getId());
        }
    }

    protected function getParentObjectInfo(): ParentObjectInfo
    {
        return new ParentObjectInfo('model-id', Model::class);
    }
}