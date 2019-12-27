<?php

namespace App\Controllers;

use App\Entity\{BioquantityVariable,
    ExperimentEvent,
    ExperimentEventArg,
    ExperimentEventVarType,
    ExperimentVariable,
    ExperimentValues,
    ExperimentNote,
    IdentifiedObject,
    Repositories\ExperimentEventArgRepository,
    Repositories\ExperimentEventRepository,
    Repositories\ExperimentEventVarTypeRepository,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ExperimentVariableRepository,
    Repositories\UnitRepository};
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
 * @property-read ExperimentEventArgRepository $repository
 * @method ExperimentEventArg getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentEventArgController extends ParentedRepositoryController
{
	/** @var ExperimentEventArgRepository */
	private $eventArgRepository;
    private $eventVarTypeRepository;

    public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->eventArgRepository = $v->get(ExperimentEventArgRepository::class);
        $this->eventVarTypeRepository = $v->get(ExperimentEventVarTypeRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'value'];
	}

	protected function getData(IdentifiedObject $eventArg): array
	{
		/** @var ExperimentEventArg $eventArg */
		return [
		    'id' => $eventArg->getId(),
			'type' => $eventArg->getTypeId() != null ? ExperimentEventVarTypeController::getData($eventArg->getTypeId()):null,
			'value' => $eventArg->getValue(),
		];
	}

	protected function setData(IdentifiedObject $eventArg, ArgumentParser $data): void
	{
		/** @var ExperimentEventArg $eventArg */
        $eventArg->getEventId() ?: $eventArg->setEventId($this->repository->getParent());
		!$data->hasKey('typeId') ?: $eventArg->setTypeId($this->eventVarTypeRepository->get($data->getInt('typeId')));
		!$data->hasKey('value') ?: $eventArg->setValue($data->getString('value'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('value'))
			throw new MissingRequiredKeyException('value');
		return new ExperimentEventArg;
	}

	protected function checkInsertObject(IdentifiedObject $eventArg): void
	{
		/** @var ExperimentEventArg $eventArg */
		if ($eventArg->getEventId() === null)
			throw new MissingRequiredKeyException('eventId');
		if ($eventArg->getValue() === null)
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
		return 'eventArg';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentEventArgRepository::Class;
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
