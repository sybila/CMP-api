<?php


namespace App\Controllers;


use App\Entity\AnalysisMethod;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnalysisMethodRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

class AnalysisMethodController extends WritableRepositoryController
{

    protected static function getRepositoryClassName(): string
    {
        return AnalysisMethodRepository::class;
    }

    protected static function getObjectName(): string
    {
        return 'analysisTool';
    }

    protected function getData(IdentifiedObject $object): array
    {
        /** @var AnalysisMethod $object */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'description' => $object->getDescription(),
            'annotation' => $object->getAnnotation(),
            'method_signature' => $object->getMethodSignature()
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
        /** @var AnalysisMethod $analysisTool */
        if ($body->hasKey('name'))
            $analysisTool->setName($body->getString('name'));
        if ($body->hasKey('description'))
            $analysisTool->setDescription($body->getString('description'));
        if ($body->hasKey('annotation'))
            $analysisTool->setAnnotation($body->getString('annotation'));
        if ($body->hasKey('method_signature'))
            $analysisTool->setMethodSignature($body->getString('method_signature'));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        return new AnalysisMethod;
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        /** @var AnalysisMethod $analysisTool */
        if ($analysisTool->getName() == '')
            throw new MissingRequiredKeyException('name');
        if ($analysisTool->getDescription() == '')
            throw new MissingRequiredKeyException('description');
        if ($analysisTool->getAnnotation() == '')
            throw new MissingRequiredKeyException('annotation');
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Type(['Tool' => 'string']),
            'description' => new Assert\Type(['Tool' => 'string']),
            'annotation' => new Assert\Type(['Tool' => 'string']),
            'method_signature' => new Assert\Json(),
        ]);
    }
}