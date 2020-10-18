<?php

namespace App\Controllers;

use AnalysisControllerCommonable;
use App\Entity\AnalysisType;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnalysisTypeRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read AnalysisTypeRepository $repository
 * @method AnalysisType getObject(int $id)
 */
class AnalysisTypeController extends WritableRepositoryController
{

    use AnalysisControllerCommonable;

    protected static function getRepositoryClassName(): string
    {
        return AnalysisTypeRepository::class;
    }

    protected static function getObjectName(): string
    {
        return 'analysisType';
    }

    protected function getData(IdentifiedObject $object): array
    {
        /** @var AnalysisType $object */
        return $this->getCommonAnalysisData($object);
    }

    protected static function getAllowedSort(): array
    {
        return ['id', 'name'];
    }

    protected function setData(IdentifiedObject $object, ArgumentParser $body): void
    {
        $this->setCommonAnalysisData($object,$body);
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        return new AnalysisType;
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        /** @var AnalysisType $analysisType */
        if ($analysisType->getName() == '')
            throw new MissingRequiredKeyException('name');
        if ($analysisType->getDescription() == '')
            throw new MissingRequiredKeyException('description');
        if ($analysisType->getAnnotation() == '')
            throw new MissingRequiredKeyException('annotation');
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection($this->getCommonAnalysisDataValidator());
    }
}