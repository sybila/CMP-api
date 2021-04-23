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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Radoslav Doktor from 2018
 * @property-read ModelEventAssignmentRepository $repository
 * @method ModelEventAssignment getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelEventAssignmentController extends ParentedRepositoryController
    implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;


	protected static function getAllowedSort(): array
	{
		return ['id', 'variable'];
	}

	protected function getData(IdentifiedObject $eventAssignment): array
	{
		/** @var ModelEventAssignment $eventAssignment */
		$sBaseData = $this->getSBaseData($eventAssignment);
		return array_merge($sBaseData, [
		    'variable' => $eventAssignment->getVariable()->getAlias(),
			'formula' => ['latex' => $eventAssignment->getFormula()->getLatex(),
                'cmml' => $eventAssignment->getFormula()->getContentMML()]
		]);
	}

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        $eventAssignment = new ModelEventAssignment();
        $eventAssignment->setEvent($this->repository->getParent());
        return $eventAssignment;
    }

	protected function setData(IdentifiedObject $eventAssignment, ArgumentParser $data): void
	{
		/** @var ModelEventAssignment $eventAssignment */
        $this->setSBaseData($eventAssignment, $data);
		if ($data->hasKey('variableType')) {
            $eventAssignment->setVariableType($data->getString('variableType'));
            if ($data->hasKey('variableId')) {
                $eventAssignment->setVariable($data->get('variableId'));
            } else {
                throw new MissingRequiredKeyException('variableId');
            }
        }
        if ($data->hasKey('formula')) {
            $formula = $data->get('formula');
            !key_exists('latex', $formula) ?: $eventAssignment->setFormula('latex', $formula['latex']);
            !key_exists('cmml', $formula) ?: $eventAssignment->setFormula('cmml', $formula['cmml']);
        }
    }

	protected function checkInsertObject(IdentifiedObject $eventAssignment): void
	{
		/** @var ModelEventAssignment $eventAssignment */
		if ($eventAssignment->getFormula() == null)
			throw new MissingRequiredKeyException('formula');
        if ($eventAssignment->getVariableType() == null)
            throw new MissingRequiredKeyException('variableType');
        if ($eventAssignment->getVariable() == null)
            throw new MissingRequiredKeyException('variableId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
        $this->deleteAnnotations($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
		    'variableType' => new Assert\Type(['type' => 'string']),
            'variableId' => new Assert\Type(['type' => 'int'])
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
        /** @var ModelEventAssignment $child */
        if ($parent->getId() != $child->getEvent()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
