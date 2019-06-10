<?php

namespace App\Controllers;

use App\Entity\{ModelChange,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ModelChangeRepository,
    Repositories\ModelReactionItemRepository};
use App\Exceptions\
{
    MissingRequiredKeyException,
    NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\{
    Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelChangeRepository $repository
 * @method ModelChange getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class ModelChangeController extends ParentedRepositoryController
{
    /** @var ModelChangeRepository */
    private $changeRepository;

    /** @var EntityManager * */
    protected $em;

    public function __construct(Container $c)
    {
        parent::__construct($c);
        $this->changeRepository = $c->get(ModelChangeRepository::class);
    }

    protected static function getAllowedSort(): array
    {
        return ['id', 'name'];
    }

    protected function getData(IdentifiedObject $change): array
    {
        /** @var ModelChange $change */
        return [
            'id' => $change->getId(),
            'originId' => $change->getOriginId(),
            'taskId' => $change->getTaskId(),
            'type' => $change->getType(),
            'value' => $change->getValue()
        ];
    }

    public function delete(Request $request, Response $response, ArgumentParser $args): Response
    {
        return parent::delete($request, $response, $args);
    }

    protected function getValidator(): Assert\Collection
    {
        $validatorArray = parent::getValidatorArray();
        return new Assert\Collection(array_merge($validatorArray, [
            'name' => new Assert\Type(['type' => 'string']),
        ]));
    }

    protected static function getObjectName(): string
    {
        return 'change';
    }

    protected static function getRepositoryClassName(): string
    {
        return ModelChangeRepository::class;
    }

    protected function setData(IdentifiedObject $change, ArgumentParser $data): void
    {
        /** @var ModelChange change */
        parent::setData($change, $data);
        !$data->hasKey('type') ?: $change->setType($data->getString('type'));
        !$data->hasKey('value') ?: $change->setValue($data->getInt('value'));
    }

}

final class TaskParentedChangeController extends ModelChangeController
{

    protected static function getParentRepositoryClassName(): string
    {
        return ModelChangeRepository::class;
    }

    protected function getParentObjectInfo(): array
    {
        return ['task-id', 'task'];
    }

    protected function setData(IdentifiedObject $change, ArgumentParser $data): void
    {
        /** @var ModelChange $change */
        $change->getTaskId() ?: $change->setTaskId((int)$this->repository->getParent());
        parent::setData($change, $data);
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        if (!$body->hasKey('specieId') && !$body->hasKey('parameterId'))
            throw new MissingRequiredKeyException('specieId or parameterId');
        return new ModelChange;
    }

    /**
     * Check object to be inserted if it contains all required fields
     * @param IdentifiedObject $object
     */
    protected function checkInsertObject(IdentifiedObject $object): void
    {
        // TODO: Implement checkInsertObject() method.
    }
}
