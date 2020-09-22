<?php

namespace App\Controllers;

use App\Entity\{Model,
    ModelEvent,
    ModelEventAssignment,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ModelRepository,
    Repositories\ModelEventRepository};
use App\Exceptions\{DependentResourcesBoundException, MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelEventRepository $repository
 * @method ModelEvent getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelEventController extends ParentedRepositoryController
{
    use \SBaseController;

	protected static function getAlias(): string
    {
        return 'e';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $event): array
	{
		/** @var ModelEvent $event */
		$sBaseData = $this->getSBaseData($event);
		return array_merge($sBaseData, [
			'delay' => $event->getDelay(),
			'trigger' => $event->getTrigger(),
			'priority' => $event->getPriority(),
			'evaluateOnTrigger' => $event->getEvaluateOnTrigger(),
			'eventAssignments' => $event->getEventAssignments()->map(function (ModelEventAssignment $eventAssignment) {
				return ['id' => $eventAssignment->getId(), 'formula' => $eventAssignment->getFormula()];
			})->toArray(),
		]);
	}

	protected function setData(IdentifiedObject $event, ArgumentParser $data): void
	{
		/** @var ModelEvent $event */
		$this->setSBaseData($event, $data);
		$event->getModelId() ?: $event->setModelId($this->repository->getParent()->getId());
		!$data->hasKey('delay') ?: $event->setDelay($data->getString('delay'));
		!$data->hasKey('trigger') ?: $event->setTrigger($data->getString('trigger'));
		!$data->hasKey('priority') ?: $event->setPriority($data->getString('priority'));
		!$data->hasKey('evaluateOnTrigger') ?: $event->setEvaluateOnTrigger($data->getString('evaluateOnTrigger'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelEvent;
	}

	protected function checkInsertObject(IdentifiedObject $event): void
	{
		/** @var ModelEvent $event */
		if ($event->getModelId() == null)
			throw new MissingRequiredKeyException('modelId');
		if ($event->getTrigger() == null)
			throw new MissingRequiredKeyException('trigger');
		if ($event->getEvaluateOnTrigger() == null)
			throw new MissingRequiredKeyException('evaluateOnTrigger');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$event = $this->getObject($args->getInt('id'));
		if (!$event->getEventAssignments()->isEmpty())
			throw new DependentResourcesBoundException('event');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
			'modelId' => new Assert\Type(['type' => 'integer']),
			'evaluateOnTrigger' => new Assert\Type(['type' => 'integer']),
			'trigger' => new Assert\Type(['type' => 'string']),
			'delay' => new Assert\Type(['type' => 'string']),
			'priority' => new Assert\Type(['type' => 'string']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelEvent';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelEventRepository::Class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('model-id', Model::class);
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        // TODO: Implement checkParentValidity() method.
    }
}
