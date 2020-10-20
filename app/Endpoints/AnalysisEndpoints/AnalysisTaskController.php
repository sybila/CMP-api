<?php


namespace App\Controllers;

use AnalysisControllerCommonable;
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

    use AnalysisControllerCommonable;

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
     */
    protected function getData(IdentifiedObject $task): array
    {
        /** @var AnalysisTask $task */
        /** @var Model|Experiment $analObj */
        $analObj = $this->repository->getParent();
        $commonAnalysisData = $this->getCommonAnalysisData($task);
        return array_merge($commonAnalysisData, [
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
        ]);
    }

    protected static function getAllowedSort(): array
    {
        return ['id', 'name'];
    }

    protected function setData(IdentifiedObject $task, ArgumentParser $body): void
    {
        /** @var AnalysisTask $task */
        $this->setCommonAnalysisData($task, $body);
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
        return new Assert\Collection($this->getCommonAnalysisDataValidator());
    }


    //--- THIS IS A PARENTAL GUIDANCE MOMENT PLEASE STEP OUT

    /**
     * A task cannot have defined parentEntity class name in ParentObjectInfo object, since
     * AnalysisTask can be parented by different entity classes (Model, Experiment).
     * Instead, this entity class can be obtained via argument obtained from the SLIM route,
     * defined as 'obj-type' in the SLIM route.
     * @return ParentObjectInfo
     */
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
            $entType = $args->get($info->parentEntityClass);
            $id = $args->get($info->parentIdRoutePlaceholder);
            $entClassName = 'App\Entity\\' . ucfirst($entType);
        } catch (Exception $e) {
            throw new MissingRequiredKeyException($e->getMessage());
        }
        return $this->getObjectViaORM($entClassName, $id);
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

    public function hasAccessToObject(array $userGroups): ?int
    {
        return parent::hasAccessToObject($userGroups);
    }
}