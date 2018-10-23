<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelCompartment,
	ModelReactionItem,
	ModelSpecie,
	IdentifiedObject,
	Repositories\ClassificationRepository,
	Repositories\EntityRepository,
	Repositories\SpecieRepository,
	Repositories\IEndpointRepository,
	Repositories\OrganismRepository,
	Repositories\ModelRepository,
	Repositories\CompartmentRepository,
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
 * @property-read SpecieRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class SpecieController extends ParentedRepositoryController
{

	/** @var SpecieRepository */
	private $specieRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->specieRepository = $c->get(SpecieRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $specie): array
	{
		/** @var ModelSpecie $specie */
		return [
			'id' => $specie->getId(),
			'name' => $specie->getName(),
			'equationType' => $specie->getEquationType(),
			'initialExpression' => $specie->getInitialExpression(),
			'hasOnlySubstanceUnits' => $specie->getHasOnlySubstanceUnits(),
			'isConstant' => $specie->getIsConstant(),
			'boundaryCondition' => $specie->getBoundaryCondition(),
			'reactionItems' => $specie->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
		];
	}

	protected function setData(IdentifiedObject $specie, ArgumentParser $data): void
	{
		/** @var ModelSpecie $specie */
		$specie->setModelId($this->repository->getParent()->getModelId()->getId());
		if (!$specie->getCompartmentId())
			$specie->setCompartmentId($this->repository->getParent());
		if ($data->hasKey('name'))
			$specie->setName($data->getString('name'));
		if ($data->hasKey('equationType'))
			$specie->setEquationType($data->getString('equationType'));
		if ($data->hasKey('initialExpression'))
			$specie->setInitialExpression($data->getString('initialExpression'));
		if ($data->hasKey('boundaryCondition'))
			$specie->setBoundaryCondition($data->getString('boundaryCondition'));
		if ($data->hasKey('hasOnlySubstanceUnits'))
			$specie->setHasOnlySubstanceUnits($data->getString('hasOnlySubstanceUnits'));
		if ($data->hasKey('isConstant'))
			$specie->setIsConstant($data->getInt('isConstant'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelSpecie;
	}

	protected function checkInsertObject(IdentifiedObject $object): void
	{
		//todo
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
			'name' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'specie';
	}

	protected static function getRepositoryClassName(): string
	{
		return SpecieRepository::Class;
	}


	protected static function getParentRepositoryClassName(): string
	{
		return CompartmentRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['compartment-id', 'compartment'];
	}
}
