<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelCompartment,
	ModelUnitToDefinition,
	ModelSpecie,
	ModelReaction,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelCompartmentRepository,
	Repositories\ModelReactionRepository,
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
 * @property-read ModelCompartmentRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelCompartmentController extends ParentedRepositoryController
{

	/** @var ModelCompartmentRepository */
	private $compartmentRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->compartmentRepository = $c->get(ModelCompartmentRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $compartment): array
	{
		/** @var ModelCompartment $compartment */
		return [
			'id' => $compartment->getId(),
			'name' => $compartment->getName(),
			'spatialDimensions' => $compartment->getSpatialDimensions(),
			'size' => $compartment->getSize(),
			'isConstant' => $compartment->getIsConstant(),
			'species' => $compartment->getSpecies()->map(function (ModelSpecie $specie) {
				return ['id' => $specie->getId(), 'name' => $specie->getName()];
			})->toArray(),
			'reactions' => $compartment->getReactions()->map(function (ModelReaction $reaction) {
				return ['id' => $reaction->getId(), 'name' => $reaction->getName()];
			})->toArray(),
		];
	}

	protected function setData(IdentifiedObject $compartment, ArgumentParser $data): void
	{
		/** @var ModelCompartment $compartment */
		$compartment->getModelId() ?: $compartment->setModelId($this->repository->getParent());
		!$data->hasKey('name') ?: $compartment->setName($data->getString('name'));
		!$data->hasKey('spatialDimensions') ?: $compartment->setSpatialDimensions($data->getString('spatialDimensions'));
		!$data->hasKey('size') ?: $compartment->setSize($data->getString('size'));
		!$data->hasKey('isConstant') ?: $compartment->setIsConstant($data->getInt('isConstant'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('isConstant'))
			throw new MissingRequiredKeyException('isConstant');

		return new ModelCompartment;
	}

	protected function checkInsertObject(IdentifiedObject $compartment): void
	{
		/** @var ModelCompartment $compartment */
		if ($compartment->getModelId() == NULL)
			throw new MissingRequiredKeyException('modelId');
		if ($compartment->getIsConstant() == NULL)
			throw new MissingRequiredKeyException('isConstant');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$compartment = $this->getObject($args->getInt('id'));
		if (!$compartment->getSpecies()->isEmpty())
			throw new DependentResourcesBoundException('specie');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'modelId' => new Assert\Type(['type' => 'integer']),
			'isConstant' => new Assert\Type(['type' => 'integer']),
			'name' => new Assert\Type(['type' => 'string']),
			'spatialDimensions' => new Assert\Type(['type' => 'integer']),
			'size' => new Assert\Type(['type' => 'integer']),
			'isConstant' => new Assert\Type(['type' => 'integer']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'modelCompartment';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelCompartmentRepository::Class;
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
