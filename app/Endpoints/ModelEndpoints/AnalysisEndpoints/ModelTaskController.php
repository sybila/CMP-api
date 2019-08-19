<?php

namespace App\Controllers;

use App\Entity\{AnalysisToolSetting,
    Model,
    IdentifiedObject,
    ModelTask,
    Repositories\IEndpointRepository,
    Repositories\ModelTaskRepository};
use App\Exceptions\{
    DependentResourcesBoundException,
    MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use App\Entity\ModelChange;
use Slim\Container;
use Slim\Http\{
    Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @property-read ModelTaskRepository $repository
 * @method ModelTask getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelTaskController extends WritableRepositoryController
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

    protected function getData(IdentifiedObject $modelTask): array
    {
        /** @var ModelTask $modelTask */
        return [
            'name' => $modelTask->getName(),
            'userId' => $modelTask->getUserId(),
            'modelId' => $modelTask->getModelId(),
            'analysisToolId' => $modelTask->getAnalysisToolId(),
            'isPostponed' => $modelTask->getIsPostponed(),
            'isPublic' => $modelTask->getIsPublic(),
            'outputPath' => $modelTask->getOutputPath(),
            'settings' => $modelTask->getAnalysisToolSettings()->map(function (AnalysisToolSetting $analSet) {
                return ['id' => $analSet->getId(), 'name' => $analSet->getName(), 'value' => $analSet->getValue()];
                })->toArray(),
            'modelChanges' => $modelTask->getModelChanges()->map(function (ModelChange $modelChange) {
                return ['type' => $modelChange->getType(), 'origin' => $modelChange->getOriginId(), 'value' => $modelChange->getValue()];
                })->toArray()
        ];
    }

    protected function setData(IdentifiedObject $modelTask, ArgumentParser $data): void
    {
        /** @var ModelTask $modelTask */
        parent::setData($modelTask, $data);
        !$data->hasKey('userId') ?: $modelTask->setUserId($data->getString('userId'));
        !$data->hasKey('modelId') ?: $modelTask->setModelId($data->getString('modelId'));
        //!$data->hasKey('description') ?: $modelTask->setDescription($data->getString('description'));
    }

    protected function createObject(\App\Helpers\ArgumentParser $body): \App\Entity\IdentifiedObject
    {
        if (!$body->hasKey('modelId'))
            throw new MissingRequiredKeyException('modelId');
        if (!$body->hasKey('analysisToolId'))
            throw new MissingRequiredKeyException('analysisToolId');
        return new Model;
    }


    protected function checkInsertObject(\App\Entity\IdentifiedObject $object): void
    {
        /** @var ModelTask $modelTask */
        if ($modelTask->getUserId() === null)
            throw new MissingRequiredKeyException('userId');
    }

    public function delete(Request $request, Response $response, ArgumentParser $args): Response
    {
        /** @var ModelTask $modelTask */
        $model = $this->getObject($args->getInt('id'));
        if (!$model->getModelChanges()->isEmpty())
            throw new DependentResourcesBoundException('modelChanges');
        if (!$model->getAnalysisToolSettings()->isEmpty())
            throw new DependentResourcesBoundException('analysisToolSettings');
        return parent::delete($request, $response, $args);
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

    protected static function getObjectName(): string
    {
        return 'modelTask';
    }

    protected static function getRepositoryClassName(): string
    {
        return ModelTaskRepository::Class;
    }

}