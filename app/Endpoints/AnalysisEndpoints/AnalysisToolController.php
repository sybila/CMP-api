<?php

namespace App\Controllers;

use AnalysisCommonableController;
use App\Entity\AnalysisTool;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnalysisToolRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read AnalysisToolRepository $repository
 * @method AnalysisTool getObject(int $id)
 */
class AnalysisToolController extends WritableRepositoryController
{

    use AnalysisCommonableController;

    protected static function getRepositoryClassName(): string
    {
        return AnalysisToolRepository::class;
    }

    protected static function getObjectName(): string
    {
        return 'analysisTool';
    }

    protected function getData(IdentifiedObject $object): array
    {
        return $this->getCommonAnalysisData($object);
    }

    protected static function getAlias(): string
    {
        return 'at';
    }

    protected static function getAllowedSort(): array
    {
        return ['id', 'name'];
    }

    protected function setData(IdentifiedObject $object, ArgumentParser $body): void
    {
        $this->setCommonAnalysisData($object, $body);
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        return new AnalysisTool;
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        /** @var AnalysisTool $analysisTool */
        if ($analysisTool->getName() == '')
            throw new MissingRequiredKeyException('name');
        if ($analysisTool->getDescription() == '')
            throw new MissingRequiredKeyException('description');
        if ($analysisTool->getAnnotation() == '')
            throw new MissingRequiredKeyException('annotation');
        if ($analysisTool->getAnnotation() == '')
            throw new MissingRequiredKeyException('location');
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection($this->getCommonAnalysisDataValidator());
    }
}