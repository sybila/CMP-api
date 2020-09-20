<?php

namespace App\Controllers;

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
        /** @var AnalysisTool $object */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'description' => $object->getDescription(),
            'annotation' => $object->getAnnotation(),
            //'location' => $object->getLocation()
        ];
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
        /** @var AnalysisTool $analysisTool */
        if ($body->hasKey('name'))
            $analysisTool->setName($body->getString('name'));
        if ($body->hasKey('description'))
            $analysisTool->setDescription($body->getString('description'));
        if ($body->hasKey('annotation'))
            $analysisTool->setAnnotation($body->getString('annotation'));
        if ($body->hasKey('location'))
            $analysisTool->setAnnotation($body->getString('annotation'));
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
        return new Assert\Collection([
            'name' => new Assert\Type(['Tool' => 'string']),
            'description' => new Assert\Type(['Tool' => 'string']),
            'annotation' => new Assert\Type(['Tool' => 'string']),
        ]);
    }
}