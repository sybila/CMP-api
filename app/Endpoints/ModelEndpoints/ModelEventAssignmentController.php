<?php

namespace App\Controllers;

use App\Entity\{
	ModelEventAssignment,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelEventRepository,
	Repositories\ModelEventAssignmentRepository
};
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
 * @property-read ModelEventAssignmentRepository $repository
 * @method EventAssignment getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelEventAssignmentController extends ParentedSBaseController
{

	/** @var ModelEventAssignmentRepository */
	private $eventAssignmentRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->eventAssignmentRepository = $c->get(ModelEventAssignmentRepository::class);
	}

    protected static function getAlias(): string
    {
        return 'e';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $eventAssignment): array
	{
		/** @var ModelEventAssignment $eventAssignment */
		$sBaseData = parent::getData($eventAssignment);
		return array_merge($sBaseData, [
			'formula' => $eventAssignment->getFormula()
		]);
	}

	protected function setData(IdentifiedObject $eventAssignment, ArgumentParser $data): void
	{
		/** @var ModelEventAssignment $eventAssignment */
		parent::setData($eventAssignment, $data);
		$eventAssignment->getEventId() ?: $eventAssignment->setEventId($this->repository->getParent());
		!$data->hasKey('formula') ?: $eventAssignment->setFormula($data->getString('formula'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelEventAssignment;
	}

	protected function checkInsertObject(IdentifiedObject $eventAssignment): void
	{
		/** @var ModelEventAssignment $eventAssignment */
		if ($eventAssignment->getEventId() == null)
			throw new MissingRequiredKeyException('eventId');
		if ($eventAssignment->getFormula() == null)
			throw new MissingRequiredKeyException('formula');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'eventId' => new Assert\Type(['type' => 'integer']),
			'formula' => new Assert\Type(['type' => 'string']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelEventAssignment';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelEventAssignmentRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ModelEventRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['event-id', 'event'];
	}
}
