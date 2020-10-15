<?php

namespace App\Controllers;

use IGroupRoleAuthWritableController;
use App\Entity\{Model,
    ModelFunctionDefinition,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ModelFunctionDefinitionRepository};
use App\Exceptions\{MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Http\{
	Request, Response
};
use SBaseControllerCommonable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelFunctionDefinitionRepository $repository
 * @method ModelFunctionDefinition getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelFunctionDefinitionController extends ParentedRepositoryController
    implements IGroupRoleAuthWritableController
{

    use SBaseControllerCommonable;

	protected static function getAlias(): string
    {
        return 'f';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $functionDefinition): array
	{
		/** @var ModelFunctionDefinition $functionDefinition */
		$sBaseData = $this->getSBaseData($functionDefinition);
		return array_merge($sBaseData, [
			'formula' => $functionDefinition->getFormula(),
		]);
	}

	protected function setData(IdentifiedObject $functionDefinition, ArgumentParser $data): void
	{
		/** @var ModelFunctionDefinition $functionDefinition */
        $this->setSBaseData($functionDefinition, $data);
		$functionDefinition->getModelId() ?: $functionDefinition->setModelId($this->repository->getParent()->getId());
		!$data->hasKey('formula') ?: $functionDefinition->setFormula($data->getString('formula'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelFunctionDefinition();
	}

	protected function checkInsertObject(IdentifiedObject $functionDefinition): void
	{
		/** @var ModelFunctionDefinition $functionDefinition */
		if ($functionDefinition->getModelId() == null)
			throw new MissingRequiredKeyException('modelId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
			'formula' => new Assert\Type(['type' => 'string'])
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelFunctionDefinition';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelFunctionDefinitionRepository::Class;
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
