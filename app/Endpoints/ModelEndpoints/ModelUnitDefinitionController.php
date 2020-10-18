<?php

namespace App\Controllers;

use App\Entity\{Model,
    ModelCompartment,
    ModelUnit,
    ModelUnitDefinition,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ModelRepository,
    Repositories\ModelUnitDefinitionRepository};
use App\Exceptions\{MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use SBaseControllerCommonable;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelUnitDefinitionRepository $repository
 * @method ModelUnitDefinition getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelUnitDefinitionController extends ParentedRepositoryController
{

    use SBaseControllerCommonable;

	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'symbol'];
	}

	protected function getData(IdentifiedObject $unitDefinition): array
	{
		/** @var ModelUnitDefinition $unitDefinition */
		$sBaseData = $this->getSBaseData($unitDefinition);
		return array_merge($sBaseData, [
			'symbol' => $unitDefinition->getSymbol(),
			'compartmentId' => $unitDefinition->getCompartmentId() ? $unitDefinition->getCompartmentId()->getId() : null,
			'units' => $unitDefinition->getUnits()->map(function (ModelUnit $units) {
				return ['id' => $units->getId(), 'name' => $units->getName()];
			})->toArray(),
		]);
	}

	protected function setData(IdentifiedObject $unitDefinition, ArgumentParser $data): void
	{
		/** @var ModelUnitDefinition $unitDefinition */
        $this->setSBaseData($unitDefinition, $data);
		$unitDefinition->getModelId() ?: $unitDefinition->setModelId($this->repository->getParent());
		!$data->hasKey('name') ?: $unitDefinition->setName($data->getString('name'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelUnitDefinition;
	}

	protected function checkInsertObject(IdentifiedObject $unitDefinition): void
	{
		/** @var ModelUnitDefinition $unitDefinition */
		if ($unitDefinition->getModelId() == null)
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
			'modelId' => new Assert\Type(['type' => 'integer']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelUnitDefinition';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelUnitDefinitionRepository::Class;
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
