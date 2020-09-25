<?php


namespace App\Controllers;


use AnalysisCommonableController;
use App\Entity\AnalysisMethod;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnalysisMethodRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

class AnalysisMethodController extends WritableRepositoryController
{
    use AnalysisCommonableController;

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
        return array_merge($this->getCommonAnalysisData($object), [
            'method_signature' => $object->getMethodSignature()
        ]);
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
        /** @var AnalysisMethod $object */
        $this->setCommonAnalysisData($object, $body);
        if ($body->hasKey('method_signature'))
            $object->setMethodSignature($body->getString('method_signature'));
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
        return new Assert\Collection( array_merge(
            $this->getCommonAnalysisDataValidator(), [
            'method_signature' => new Assert\Json(),
        ]));
    }
}