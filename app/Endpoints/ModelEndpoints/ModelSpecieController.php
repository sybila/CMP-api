<?php

namespace App\Controllers;

use App\Entity\{
	ModelReactionItem,
	ModelSpecie,
	ModelRule,
	IdentifiedObject,
	Repositories\ModelSpecieRepository,
	Repositories\IEndpointRepository,
	Repositories\ModelCompartmentRepository
};
use App\Exceptions\
{
	MissingRequiredKeyException,
	DependentResourcesBoundException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelSpecieRepository $repository
 * @method ModelSpecie getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelSpecieController extends ParentedSBaseController
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

	public function readSbmlId(Request $request, Response $response, ArgumentParser $args)
	{
		$specie = $this->repository->getBySbmlId($args->getString('sbmlId'));
		return self::formatOk(
			$response,
			$specie ? $this->getData($specie) : null
		);
	}

	protected function getData(IdentifiedObject $specie): array
	{
		/** @var ModelSpecie $specie */
		$sBaseData = parent::getData($specie);
		return array_merge($sBaseData, [
			'initialExpression' => $specie->getInitialExpression(),
			'hasOnlySubstanceUnits' => $specie->getHasOnlySubstanceUnits(),
			'isConstant' => $specie->getIsConstant(),
			'boundaryCondition' => $specie->getBoundaryCondition(),
			'reactionItems' => $specie->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'rules' => $specie->getRules()->map(function (ModelRule $rule) {
				return ['id' => $rule->getId(), 'equation' => $rule->getEquation()];
			})->toArray()
		]);
	}

	protected function setData(IdentifiedObject $specie, ArgumentParser $data): void
	{
		/** @var ModelSpecie $specie */
		parent::setData($specie, $data);
		$specie->setModelId($this->repository->getParent()->getModelId()->getId());
		$specie->getCompartmentId() ?: $specie->setCompartmentId($this->repository->getParent());
		!$data->hasKey('initialExpression') ?: $specie->setInitialExpression($data->getString('initialExpression'));
		!$data->hasKey('boundaryCondition') ?: $specie->setBoundaryCondition($data->getInt('boundaryCondition'));
		!$data->hasKey('hasOnlySubstanceUnits') ?: $specie->setHasOnlySubstanceUnits($data->getInt('hasOnlySubstanceUnits'));
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
		if ($specie->getModelId() === null)
			throw new MissingRequiredKeyException('modelId');
		if ($specie->getCompartmentId() === null)
			throw new MissingRequiredKeyException('compartmentId');
		if ($specie->getHasOnlySubstanceUnits() === null)
			throw new MissingRequiredKeyException('hasOnlySubstanceUnits');
		if ($specie->getIsConstant() === null)
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
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'equationType' => new Assert\Type(['type' => 'string']),
			'initialExpression' => new Assert\Type(['type' => 'string']),
			'boundaryCondition' => new Assert\Type(['type' => 'integer']),
			'hasOnlySubstanceUnits' => new Assert\Type(['type' => 'integer']),
			'isConstant' => new Assert\Type(['type' => 'integer']),
		]));
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
