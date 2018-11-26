<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelCompartment,
	ModelUnit,
	ModelUnitDefinition,
	ModelUnitToDefinition,
	ModelSpecie,
	ModelReaction,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelUnitDefinitionRepository,
	Structure
};
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
 * @property-read ModelUnitDefinitionRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelUnitDefinitionController extends ParentedRepositoryController
{

	/** @var ModelUnitDefinitionRepository */
	private $unitDefinitionRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->unitDefinitionRepository = $c->get(ModelUnitDefinitionRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'symbol'];
	}

	protected function getData(IdentifiedObject $unitDefinition): array
	{
		/** @var ModelUnitDefinition $unitDefinition */
		return [
			'id' => $unitDefinition->getId(),
			'name' => $unitDefinition->getName(),
			'symbol' => $unitDefinition->getSymbol(),
			'compartmentId' => $unitDefinition->getCompartmentId()->getId(),
			'units' => $unitDefinition->getUnits()->map(function (ModelUnit $units) {
				return ['id' => $units->getId(), 'name' => $units->getName()];
			})->toArray(),
		];
	}

	protected function setData(IdentifiedObject $unitDefinition, ArgumentParser $data): void
	{
		/** @var ModelUnitDefinition $unitDefinition */
		$unitDefinition->getModelId() ?: $unitDefinition->setModelId($this->repository->getParent());
		!$data->hasKey('name') ?: $unitDefinition->setName($data->getString('name'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelCompartment;
	}

	protected function checkInsertObject(IdentifiedObject $unitDefinition): void
	{
		/** @var ModelUnitDefinition $unitDefinition */
		if ($unitDefinition->getModelId() == NULL)
			throw new MissingRequiredKeyException('modelId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'modelId' => new Assert\Type(['type' => 'integer']),
			'name' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'modelUnitDefinition';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelUnitDefinitionRepository::Class;
	}


	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['model-id', 'model'];
	}
}
