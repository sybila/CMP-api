<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use App\Entity\{Model,
    ModelInitialAssignment,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ModelInitialAssignmentRepository};
use App\Exceptions\{MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use SBaseControllerCommonable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelInitialAssignmentRepository $repository
 * @method ModelInitialAssignment getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelInitialAssignmentController extends ParentedRepositoryController
    implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $initialAssignment): array
	{
		/** @var ModelInitialAssignment $initialAssignment */
		$sBaseData = $this->getSBaseData($initialAssignment);
		return array_merge($sBaseData, [
			'id' => $initialAssignment->getId(),
			'formula' => $initialAssignment->getFormula(),
		]);
	}

	protected function setData(IdentifiedObject $initialAssignment, ArgumentParser $data): void
	{
		/** @var ModelInitialAssignment $initialAssignment */
		$this->setSBaseData($initialAssignment, $data);
		$initialAssignment->getModelId() ?: $initialAssignment->setModelId($this->repository->getParent()->getId());
		!$data->hasKey('formula') ?: $initialAssignment->setFormula($data->getString('formula'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelInitialAssignment();
	}

	protected function checkInsertObject(IdentifiedObject $initialAssignment): void
	{
		/** @var ModelInitialAssignment $initialAssignment */
		if ($initialAssignment->getModelId() == null)
			throw new MissingRequiredKeyException('modelId');
		if ($initialAssignment->getFormula() == null)
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
			'modelId' => new Assert\Type(['type' => 'integer']),
			'formula' => new Assert\Type(['type' => 'string'])
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelInitialAssignment';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelInitialAssignmentRepository::Class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('model-id', Model::class);
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var ModelInitialAssignment $child */
        if ($parent->getId() != $child->getModelId()->getId()) {
            throw new WrongParentException($this->getParentObjectInfo()->parentEntityClass, $parent->getId(),
                self::getObjectName(), $child->getId());
        }
    }
}
