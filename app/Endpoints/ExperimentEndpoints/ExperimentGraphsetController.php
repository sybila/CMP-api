<?php


namespace App\Controllers;


use App\Entity\Experiment;
use App\Entity\ExperimentGraphset;
use App\Entity\ExpVarToGraphset;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\ExperimentGraphsetRepository;
use App\Exceptions\InvalidTypeException;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use IGroupRoleAuthWritableController;
use Symfony\Component\Validator\Constraints as Assert;

class ExperimentGraphsetController extends ParentedRepositoryController
    implements IGroupRoleAuthWritableController
{

    protected static function getAllowedSort(): array
    {
        return [''];
    }

    protected function getParentObjectInfo(): ParentObjectInfo
    {
        return new ParentObjectInfo('experiment-id', Experiment::class);
    }

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ExperimentGraphset $child */
        if ($parent->getId() != $child->getExperiment()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }

    protected static function getRepositoryClassName(): string
    {
        return ExperimentGraphsetRepository::class;
    }

    protected static function getObjectName(): string
    {
        return ExperimentGraphset::class;
    }

    protected function getData(IdentifiedObject $object): array
    {
        /** @var ExperimentGraphset $object */
        return ['id' => $object->getId(),
            'name' => $object->getName(),
            'variables' => $object->getVarToGraphset()->map(function (ExpVarToGraphset $eg) {
                return ['id' => $eg->getExpVar()->getId(), 'name' => $eg->getExpVar()->getName(),
                    'visualize' => $eg->getVisualize()];
            })->toArray()];
    }

    protected function setData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var ExperimentGraphset $object */
        $object->getExperiment() ?: $object->setExperiment($this->repository->getParent());
        !$body->hasKey('name') ?: $object->setName($body->getString('name'));
        !$body->hasKey('variables') ?: $object->setVarsToGraphset($body->get('variables'));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        return new ExperimentGraphset();
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        /** @var ExperimentGraphset $object */
        if ($object->getName() == null)
            throw new MissingRequiredKeyException('name');
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Type(['type' => 'string']),
            'variables' => new Assert\Type(['type' => 'array']),
        ]);
    }
}