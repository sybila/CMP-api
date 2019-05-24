<?php

use App\Entity\{Model,
    IdentifiedObject,
    ModelCompartment,
    ModelConstraint,
    ModelEvent,
    ModelFunctionDefinition,
    ModelInitialAssignment,
    ModelParameter,
    ModelReaction,
    ModelRule,
    ModelUnitDefinition,
    Repositories\IEndpointRepository,
    Repositories\ModelRepository,
    Repositories\ModelTaskRepository};
use App\Exceptions\{
    DependentResourcesBoundException,
    MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
    Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

final class ModelTaskController extends \App\Controllers\SBaseController
{

    /** @var ModelTaskRepository */
    private $modelTaskRepository;

    public function __construct(Container $c)
    {
        parent::__construct($c);
        $this->modelTaskRepository = $c->get(ModelTaskRepository::class);
    }


    protected static function getAllowedSort(): array
    {
        return ['id, name, userId, modelId, analysisToolId, isPostponed'];
    }

    protected function getData(IdentifiedObject $analysisTool): array
    {
        /** @var ModelTask $modelTask */
        $sBaseData = parent::getData($modelTask);
        return array_merge($sBaseData, [
            'name' => $modelTask->getName(),
            'userId' => $modelTask->getUserId(),
            'description' => $modelTask->getDescription(),
            'vizId' => $modelTask->getVizId(),
            'location' => $modelTask->getLocation(),
            'settings' => $modelTask->getAnalysisToolSettings()->map(function (AnalysisToolSetting $analSet) {
                return ['id' => $analSet->getId(), 'name' => $analSet->getName()];
            })->toArray(),
            'modelChanges' => $modelTask->getModelChanges()->map(function (ModelChange $modelChange) {
        return ['id' => $modelChange->getId(), 'name' => $modelChange->getName()];
            })->toArray()
        ]);
    }

    /**
     * Create object to be inserted, can be as simple as `return new SomeObject;`
     * @param \App\Helpers\ArgumentParser $body request body
     * @return \App\Entity\IdentifiedObject
     */
    protected function createObject(\App\Helpers\ArgumentParser $body): \App\Entity\IdentifiedObject
    {
        // TODO: Implement createObject() method.
    }

    /**
     * Check object to be inserted if it contains all required fields
     * @param \App\Entity\IdentifiedObject $object
     */
    protected function checkInsertObject(\App\Entity\IdentifiedObject $object): void
    {
        // TODO: Implement checkInsertObject() method.
    }

    protected function getValidator(): Assert\Collection
    {
        $validatorArray = parent::getValidatorArray();
        return new Assert\Collection(array_merge($validatorArray, [
            'userId' => new Assert\Type(['type' => 'integer']),
            'description' => new Assert\Type(['type' => 'string']),
            'visualisation' => new Assert\Type(['type' => 'string']),
        ]));
    }

    protected static function getRepositoryClassName(): string
    {
        return ModelTaskRepository::Class;
    }

    protected static function getObjectName(): string
    {
        return 'modelTask';
    }

    protected function getSub($entity)
    {
        echo $entity;
    }


}