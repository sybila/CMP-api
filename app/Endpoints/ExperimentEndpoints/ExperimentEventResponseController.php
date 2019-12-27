<?php

namespace App\Controllers;

use App\Entity\{ExperimentEventArg,
    ExperimentEventResponse,
    IdentifiedObject,
    Repositories\ExperimentEventRepository,
    Repositories\ExperimentEventResponseRepository,
    Repositories\ExperimentEventVarTypeRepository,
    Repositories\IEndpointRepository};
use App\Exceptions\
{
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ExperimentEventResponseRepository $repository
 * @method ExperimentEventResponse getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentEventResponseController extends ParentedRepositoryController
{
	/** @var ExperimentEventResponseRepository */
	private $eventResponseRepository;
    private $eventVarTypeRepository;

    public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->eventResponseRepository = $v->get(ExperimentEventResponseRepository::class);
        $this->eventVarTypeRepository = $v->get(ExperimentEventVarTypeRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'value'];
	}

	protected function getData(IdentifiedObject $eventResponse): array
	{
		/** @var ExperimentEventResponse $eventResponse */
		return [
		    'id' => $eventResponse->getId(),
			'type' => $eventResponse->getTypeId() != null ? ExperimentEventVarTypeController::getData($eventResponse->getTypeId()):null,
			'value' => $eventResponse->getValue(),
		];
	}

	protected function setData(IdentifiedObject $eventResponse, ArgumentParser $data): void
	{
		/** @var ExperimentEventResponse $eventResponse */
        $eventResponse->getEventId() ?: $eventResponse->setEventId($this->repository->getParent());
		!$data->hasKey('typeId') ?: $eventResponse->setTypeId($this->eventVarTypeRepository->get($data->getInt('typeId')));
		!$data->hasKey('value') ?: $eventResponse->setValue($data->getString('value'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('value'))
			throw new MissingRequiredKeyException('value');
		return new ExperimentEventResponse;
	}

	protected function checkInsertObject(IdentifiedObject $eventResponse): void
	{
		/** @var ExperimentEventResponse $eventResponse */
		if ($eventResponse->getEventId() === null)
			throw new MissingRequiredKeyException('eventId');
		if ($eventResponse->getValue() === null)
			throw new MissingRequiredKeyException('value');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection( [
			'eventId' => new Assert\Type(['type' => 'integer']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'eventResponse';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentEventResponseRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ExperimentEventRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['event-id', 'event'];
	}
}
