<?php


namespace App\Controllers;

use App\Entity\AnalysisTask;
use App\Entity\Experiment;
use App\Entity\IdentifiedObject;
use App\Entity\Model;
use App\Entity\Repositories\AnalysisTaskRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\NonExistingObjectException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

class AnalysisTaskController extends ParentedRepositoryController
{

    protected static function getRepositoryClassName(): string
    {
        return AnalysisTaskRepository::class;
    }

    protected static function getObjectName(): string
    {
        return 'analysisTask';
    }

    /**
     * @inheritDoc
     * @throws NonExistingObjectException
     */
    protected function getData(IdentifiedObject $task): array
    {
        /** @var AnalysisTask $task */
        /** @var Model|Experiment $analObj */
        $analObj = $this->getObjectViaORM($task->getObjectType(), $task->getObjectId());
        return [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'description' => $task->getDescription(),
            'annotation' => $task->getAnnotation(),
            'notes' => $task->getNotes(),
            'user_id' => $task->getUserId(),
            'analyzed_object' =>
                ['type' => $task->getObjectType(),
                    'id' => $analObj->getId(),
                    'name' => $analObj->getName(),
                    'description' => $task->getDescription()],
            'method' =>
                ['id' => $task->getMethod()->getId(),
                    'name'  => $task->getMethod()->getName()],
            'dataset' => $task->getDataset(),
            'settings' => $task->getSettings(),
            'isPublic' => $task->isPublic()
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

    protected function setData(IdentifiedObject $task, ArgumentParser $body): void
    {
        /** @var AnalysisTask $task */
        if ($body->hasKey('name'))
            $task->setName($body->getString('name'));
        if ($body->hasKey('description'))
            $task->setDescription($body->getString('description'));
        if ($body->hasKey('annotation'))
            $task->setAnnotation($body->getString('annotation'));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        return new AnalysisTask;
    }


    protected function checkInsertObject(IdentifiedObject $task): void
    {
        /** @var AnalysisTask $task */
        if ($task->getName() == '')
            throw new MissingRequiredKeyException('name');
        if ($task->getDescription() == '')
            throw new MissingRequiredKeyException('description');
        if ($task->getAnnotation() == '')
            throw new MissingRequiredKeyException('annotation');
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Type(['Tool' => 'string']),
            'description' => new Assert\Type(['Tool' => 'string']),
            'annotation' => new Assert\Type(['Tool' => 'string']),
        ]);
    }


    //--- THIS IS A PARENTAL GUIDANCE MOMENT PLEASE STEP OUT

    protected function getParentObjectInfo(): ParentObjectInfo
    {
        return new ParentObjectInfo('obj-id', 'obj-type');
    }

    /**
     * This is a function override. Needed because this controller serves two different parent types.
     * @param ArgumentParser $args
     * @return IdentifiedObject
     * @throws NonExistingObjectException|MissingRequiredKeyException
     */
    protected function getParentObject(ArgumentParser $args): IdentifiedObject
    {
        $info = $this->getParentObjectInfo();
        try {
            $type = $args->get($info->parentEntityName);
            $id = $args->get($info->parentIdRoutePlaceholder);
        } catch (Exception $e) {
            throw new MissingRequiredKeyException($e->getMessage());
        }
        return $this->getObjectViaORM($type, $id);
    }

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $data): void
    {
        /** @var AnalysisTask $data */
        if (get_class($parent) != 'App\Entity\\' . ucfirst($data->getObjectType()))
            throw new WrongParentException(get_class($parent), $parent->getId(), self::getObjectName(), $data->getId());
        if ($parent->getId() != $data->getObjectId()) {
            throw new WrongParentException($data->getObjectType(), $parent->getId(),
                self::getObjectName(), $data->getId());
        }
    }
}