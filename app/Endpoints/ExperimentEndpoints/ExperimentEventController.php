<?php

namespace App\Controllers;

use App\Entity\{Bioquantity,
    BioquantityVariable,
    Device,
    ExperimentEvent,
    ExperimentEventArg,
    ExperimentEventResponse,
    ExperimentEventType,
    ExperimentEventVarType,
    ExperimentVariable,
    ExperimentValues,
    ExperimentNote,
    IdentifiedObject,
    Repositories\DeviceRepository,
    Repositories\ExperimentEventRepository,
    Repositories\ExperimentEventTypeRepository,
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
    private $eventTypeRepository;
    private $deviceRepository;

    public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->eventRepository = $v->get(ExperimentEventRepository::class);
        $this->eventTypeRepository = $v->get(ExperimentEventTypeRepository::class);
        $this->deviceRepository = $v->get(DeviceRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'typeId', 'time', 'devicesId', 'success'];
	}

	protected function getData(IdentifiedObject $event): array
	{
		/** @var ExperimentEvent $event */
		return [
		    'id' => $event->getId(),
			'time' => $event->getTime(),
			'type' => $event->getTypeId() != null ?  ExperimentEventTypeController::getData($event->getTypeId()) : null,
			'device' => $event->getDeviceId() != null ?  DeviceController::getData($event->getDeviceId()) : null,
            'args' => $event->getArgs()->map(function (ExperimentEventArg $arg) {
                return ['id' => $arg->getId(), 'type' => $arg->getTypeId()!= null ? ExperimentEventVarTypeController::getData($arg->getTypeId()):null, 'value' => $arg->getValue()];
            })->toArray(),
            'responses' => $event->getResponses()->map(function (ExperimentEventResponse $response) {
                return ['id' => $response->getId(), 'type' => $response->getTypeId()!= null ? ExperimentEventVarTypeController::getData($response->getTypeId()):null, 'value' => $response->getValue()];
            })->toArray(),
            'success' => $event->getSuccess(),
		];
	}

	protected function setData(IdentifiedObject $event, ArgumentParser $data): void
	{
		/** @var ExperimentEvent $event */
		$event->getExperimentId() ?: $event->setExperimentId($this->repository->getParent());
		!$data->hasKey('time') ?: $event->setTime($data->getFloat('time'));
		!$data->hasKey('typeId') ?: $event->setTypeId($this->eventTypeRepository->get($data->getInt('typeId')));
		!$data->hasKey('deviceId')?: $event->setDeviceId($this->deviceRepository->get($data->getInt('deviceId')));
        !$data->hasKey('success') ?: $event->setSuccess($data->getBool('success'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('time'))
			throw new MissingRequiredKeyException('time');
		if (!$body->hasKey('typeId'))
			throw new MissingRequiredKeyException('typeId');
        if (!$body->hasKey('deviceId'))
            throw new MissingRequiredKeyException('deviceId');
        if (!$body->hasKey('success'))
            throw new MissingRequiredKeyException('success');
		return new ExperimentEvent();
	}

	protected function checkInsertObject(IdentifiedObject $event): void
	{
		/** @var ExperimentEvent $event */
		if ($event->getExperimentId() === null)
			throw new MissingRequiredKeyException('experimentId');
		if ($event->getTypeId() === null)
			throw new MissingRequiredKeyException('typeId');
		if ($event->getTime() === null)
			throw new MissingRequiredKeyException('time');
        if ($event->getDeviceId() === null)
            throw new MissingRequiredKeyException('deviceId');
        if ($event->getSuccess() === null)
            throw new MissingRequiredKeyException('success');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
        /** @var ExperimentEvent $event */
        $event = $this->getObject($args->getInt('id'));
        if (!$event->getArgs()->isEmpty())
            throw new DependentResourcesBoundException('args');
        if (!$event->getResponses()->isEmpty())
            throw new DependentResourcesBoundException('responses');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection( [
			'experimentId' => new Assert\Type(['type' => 'integer']),
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
