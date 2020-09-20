<?php


namespace App\Controllers;


use App\Entity\AnalysisSettings;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnalysisSettingsRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

class AnalysisSettingsController extends ParentedRepositoryController
{

    protected static function getRepositoryClassName(): string
    {
        return AnalysisSettingsRepository::class;
    }

    protected static function getObjectName(): string
    {
        return 'analysisSettings';
    }

    protected function getData(IdentifiedObject $object): array
    {
        /** @var AnalysisSettings $object */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'methodId' => $object->getMethodId(),
            'methodSettings' => $object->getMethodSettings()
        ];
    }

    protected static function getAllowedSort(): array
    {
        return ['id', 'name', 'methodId'];
    }

    protected static function getAlias(): string
    {
        return 's';
    }

    protected function setData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var AnalysisSettings $object */
        !$body->hasKey('name') ?: $object->setName($body->getString('name'));
        !$body->hasKey('methodSettings') ?: $object->setMethodSettings($body->getString('methodSettings'));
        $object->getMethodId() ?: $object->setMethodId($this->repository->getParent()->getId());
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        $this->verifyMandatoryArguments(['name','methodSettings'], $body);
        return new AnalysisSettings();
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        /** @var AnalysisSettings $object */
        if ($object->getName() == '')
            throw new MissingRequiredKeyException('name');
        if ($object->getMethodId() == '')
            throw new MissingRequiredKeyException('name');
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Type(['type' => 'string']),
            'methodSettings' => new Assert\Json(),
        ]);
    }

    protected function checkParentValidity(IdentifiedObject $meth, IdentifiedObject $child)
    {
        /** @var AnalysisSettings $child */
        if ($meth->getId() != $child->getMethodId()) {
            throw new WrongParentException('analysisMethod', $meth->getId(),
                self::getObjectName(), $child->getId());
        }
    }

    protected function getParentObjectInfo(): ParentObjectInfo
    {
        return new ParentObjectInfo('meth-id', 'analysisMethod');
    }
}