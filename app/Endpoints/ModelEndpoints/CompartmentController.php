<?php

namespace App\Controllers;

use App\Entity\{
	Compartment,
	Entity,
	ModelCompartment,
	ModelSpecie,
	ModelReaction,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\CompartmentRepository,
	Repositories\ReactionRepository,
	Structure
};
use App\Exceptions\
{
	CompartmentLocationException,
	InvalidArgumentException,
	MissingRequiredKeyException,
	UniqueKeyViolationException
};
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read CompartmentRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class CompartmentController extends ParentedRepositoryController
{

	/** @var CompartmentRepository */
	private $compartmentRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->compartmentRepository = $c->get(CompartmentRepository::class);
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
		if(!$compartment->getModelId())
			$compartment->setModelId($this->repository->getParent());
		if ($data->hasKey('name'))
			$compartment->setName($data->getString('name'));
		if ($data->hasKey('spatialDimensions'))
			$compartment->setSpatialDimensions($data->getString('spatialDimensions'));
		if ($data->hasKey('size'))
			$compartment->setSize($data->getString('size'));
		if ($data->hasKey('isConstant'))
			$compartment->setIsConstant($data->getInt('isConstant'));
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
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		try {
			$a = parent::delete($request, $response, $args);
		} catch (\Exception $e) {
			throw new InvalidArgumentException('annotation', $args->getString('annotation'), 'must be in format term:id');
		}
		return $a;

	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'modelId' => new Assert\Type(['type' => 'integer']),
			'isConstant' => new Assert\Type(['type' => 'integer']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'modelCompartment';
	}

	protected static function getRepositoryClassName(): string
	{
		return CompartmentRepository::Class;
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
