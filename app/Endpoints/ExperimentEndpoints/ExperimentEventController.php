<?php

namespace App\Controllers;

use App\Entity\{BioquantityVariable,
    ExperimentEvent,
    ExperimentVariable,
    ExperimentValues,
    ExperimentNote,
    IdentifiedObject,
    Repositories\ExperimentEventRepository,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ExperimentVariableRepository};
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
 * @property-read ExperimentEventRepository $repository
 * @method ExperimentEvent getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentEventController extends ParentedRepositoryController
{
	/** @var ExperimentVariableRepository */
	private $eventRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->eventRepository = $v->get(ExperimentEventRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'type', 'time', 'event'];
	}

	protected function getData(IdentifiedObject $event): array
	{
		/** @var ExperimentEvent $event */
		return [
		    'id' => $event->getId(),
			'time' => $event->getTime(),
			'event' => $event->getEvent(),
			'type' => $event->getType(),
            'isAutomatic' => $event->getIsAutomatic(),
		];
	}

	protected function setData(IdentifiedObject $event, ArgumentParser $data): void
	{
		/** @var ExperimentEvent $event */
		$event->getExperimentId() ?: $event->setExperimentId($this->repository->getParent());
		!$data->hasKey('time') ?: $event->setTime($data->getFloat('time'));
		!$data->hasKey('type') ?: $event->setType($data->getString('type'));
		!$data->hasKey('event') ?: $event->setEvent($data->getString('event'));
        !$data->hasKey('isAutomatic') ?: $event->setIsAutomatic($data->getBool('isAutomatic'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('event'))
			throw new MissingRequiredKeyException('event');
		if (!$body->hasKey('type'))
			throw new MissingRequiredKeyException('type');
        if (!$body->hasKey('time'))
            throw new MissingRequiredKeyException('time');
        if (!$body->hasKey('isAutomatic'))
            throw new MissingRequiredKeyException('isAutomatic');
		return new ExperimentEvent();
	}

	protected function checkInsertObject(IdentifiedObject $event): void
	{
		/** @var ExperimentEvent $event */
		if ($event->getExperimentId() === null)
			throw new MissingRequiredKeyException('experimentId');
		if ($event->getType() === null)
			throw new MissingRequiredKeyException('type');
		if ($event->getTime() === null)
			throw new MissingRequiredKeyException('time');
        if ($event->getEvent() === null)
            throw new MissingRequiredKeyException('event');
        if ($event->getIsAutomatic() === null)
            throw new MissingRequiredKeyException('isAutomatic');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var ExperimentEvent $event */
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection( [
			'experimentId' => new Assert\Type(['type' => 'integer']),
            'event' => new Assert\Type(['type' => 'string']),
            'type' => new Assert\Type(['type' => 'string']),
            'isAutomatic' => new Assert\Type(['type' => 'boolean']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'experimentEvent';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentEventRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ExperimentRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['experiment-id', 'experiment'];
	}
}
