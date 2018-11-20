<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelCompartment,
	ModelEvent,
	ModelEventAssignment,
	ModelSpecie,
	ModelReaction,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelCompartmentRepository,
	Repositories\ModelEventRepository,
	Repositories\ModelEventAssignmentRepository,
	Structure
};
use App\Exceptions\
{
	DependentResourcesBoundException,
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelEventRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelEventController extends ParentedRepositoryController
{

	/** @var ModelEventRepository */
	private $eventRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->eventRepository = $c->get(ModelCompartmentRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $event): array
	{
		/** @var ModelEvent $event */
		return [
			'id' => $event->getId(),
			'name' => $event->getName(),
			'delay' => $event->getDelay(),
			'trigger' => $event->getTrigger(),
			'priority' => $event->getPriority(),
			'evaluateOnTrigger' => $event->getEvaluateOnTrigger(),
			'eventAssignments' => $event->getEventAssignments()->map(function (ModelEventAssignment $eventAssignment) {
				return ['id' => $eventAssignment->getId(), 'formula' => $eventAssignment->getFormula()];
			})->toArray(),
		];
	}

	protected function setData(IdentifiedObject $event, ArgumentParser $data): void
	{
		/** @var ModelEvent $event */
		$event->getModelId() ?: $event->setModelId($this->repository->getParent());
		!$data->hasKey('name') ?: $event->setName($data->getString('name'));
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
		if ($event->getModelId() == NULL)
			throw new MissingRequiredKeyException('modelId');
		if ($event->getTrigger() == NULL)
			throw new MissingRequiredKeyException('trigger');
		if ($event->getEvaluateOnTrigger() == NULL)
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
		return new Assert\Collection([
			'modelId' => new Assert\Type(['type' => 'integer']),
			'evaluateOnTrigger' => new Assert\Type(['type' => 'integer']),
			'name' => new Assert\Type(['type' => 'string']),
			'trigger' => new Assert\Type(['type' => 'string']),
			'delay' => new Assert\Type(['type' => 'string']),
			'priority' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'modelEvent';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelEventRepository::Class;
	}


	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['model-id', 'model'];
	}
}