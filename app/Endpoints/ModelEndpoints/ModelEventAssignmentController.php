<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use App\Entity\{ModelEvent,
    ModelEventAssignment,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ModelEventAssignmentRepository};
use App\Exceptions\{MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use SBaseControllerCommonable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelEventAssignmentRepository $repository
 * @method ModelEventAssignment getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelEventAssignmentController extends ParentedRepositoryController
    implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;

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
		$sBaseData = $this->getSBaseData($eventAssignment);
		return array_merge($sBaseData, [
			'formula' => $eventAssignment->getFormula()
		]);
	}

	protected function setData(IdentifiedObject $eventAssignment, ArgumentParser $data): void
	{
		/** @var ModelEventAssignment $eventAssignment */
        $this->setSBaseData($eventAssignment, $data);
		$eventAssignment->getEventId() ?: $eventAssignment->setEventId($this->repository->getParent()->getId());
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
		$validatorArray = $this->getSBaseValidator();
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


	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('event-id', ModelEvent::class);
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        // TODO: Implement checkParentValidity() method.
    }
}
