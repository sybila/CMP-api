<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelUnitToDefinition,
	ModelReactionItem,
	ModelSpecie,
	IdentifiedObject,
	Repositories\ClassificationRepository,
	Repositories\EntityRepository,
	Repositories\ModelSpecieRepository,
	Repositories\IEndpointRepository,
	Repositories\OrganismRepository,
	Repositories\ModelRepository,
	Repositories\ModelCompartmentRepository,
	Structure
};
use App\Exceptions\
{
	CompartmentLocationException,
	InvalidArgumentException,
	MissingRequiredKeyException,
	DependentResourcesBoundException,
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
 * @property-read ModelSpecieRepository $repository
 * @method ModelSpecie getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelSpecieController extends ParentedRepositoryController
{

	/** @var ModelSpecieRepository */
	private $specieRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->specieRepository = $c->get(ModelSpecieRepository::class);
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
			'sbmlId' => $specie->getSbmlId(),
			'equationType' => $specie->getEquationType(),
			'initialExpression' => $specie->getInitialExpression(),
			'hasOnlySubstanceUnits' => $specie->getHasOnlySubstanceUnits(),
			'isConstant' => $specie->getIsConstant(),
			'boundaryCondition' => $specie->getBoundaryCondition(),
			'reactionItems' => $specie->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'rules' => $specie->getReactionItems()->map(function (ModelRule $rule) {
				return ['id' => $rule->getId(), 'equation' => $rule->getEquation()];
			})->toArray()
		];
	}

	protected function setData(IdentifiedObject $specie, ArgumentParser $data): void
	{
		/** @var ModelSpecie $specie */
		$specie->setModelId($this->repository->getParent()->getModelId()->getId());
		$specie->getCompartmentId() ?: $specie->setCompartmentId($this->repository->getParent());
		!$data->hasKey('name') ? $specie->setSbmlId($data->getString('sbmlId')) : $specie->setName($data->getString('name'));
		!$data->hasKey('sbmlId') ?: $specie->setSbmlId($data->getString('sbmlId'));
		!$data->hasKey('equationType') ?: $specie->setEquationType($data->getString('equationType'));
		!$data->hasKey('initialExpression') ?: $specie->setInitialExpression($data->getString('initialExpression'));
		!$data->hasKey('boundaryCondition') ?: $specie->setBoundaryCondition($data->getString('boundaryCondition'));
		!$data->hasKey('hasOnlySubstanceUnits') ?: $specie->setHasOnlySubstanceUnits($data->getString('hasOnlySubstanceUnits'));
		!$data->hasKey('isConstant') ?: $specie->setIsConstant($data->getInt('isConstant'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('isConstant'))
			throw new MissingRequiredKeyException('isConstant');
		if (!$body->hasKey('hasOnlySubstanceUnits'))
			throw new MissingRequiredKeyException('hasOnlySubstanceUnits');
		return new ModelSpecie;
	}

	protected function checkInsertObject(IdentifiedObject $specie): void
	{
		/** @var ModelSpecie $specie */
		if ($specie->getModelId() == null)
			throw new MissingRequiredKeyException('modelId');
		if ($specie->getCompartmentId() == null)
			throw new MissingRequiredKeyException('compartmentId');
		if ($specie->getHasOnlySubstanceUnits() == null)
			throw new MissingRequiredKeyException('hasOnlySubstanceUnits');
		if ($specie->getIsConstant() == null)
			throw new MissingRequiredKeyException('isConstant');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$specie = $this->getObject($args->getInt('id'));
		if (!$specie->getRulesItems()->isEmpty())
			throw new DependentResourcesBoundException('rule');
		if (!$specie->getReactionItems()->isEmpty())
			throw new DependentResourcesBoundException('reactionItem');
		return parent::delete($request, $response, $args);
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
		return ModelSpecieRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ModelCompartmentRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['compartment-id', 'compartment'];
	}
}
