<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	Model,
	IdentifiedObject,
	ModelUnit,
	ModelReaction,
	Repositories\IEndpointRepository,
	Repositories\ModelUnitRepository,
	Structure
};
use App\Exceptions\{
	CompartmentLocationException,
	DependentResourcesBoundException,
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
 * @property-read ModelUnitRepository $repository
 * @method ModelUnit getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelUnitController extends SBaseController
{
	/** @var ModelUnitRepository */
	private $modelUnitRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->modelUnitRepository = $c->get(ModelUnitRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id, name'];
	}

	protected function getData(IdentifiedObject $modelUnit): array
	{
		/** @var ModelUnit $modelUnit */
		$sBaseData = parent::getData($modelUnit);
		return array_merge($sBaseData, [
			'baseUnitId' => $modelUnit->getBaseUnitId(),
			'symbol' => $modelUnit->getSymbol(),
			'exponent' => $modelUnit->getExponent(),
			'multiplier' => $modelUnit->getExponent()
		]);
	}

	protected function setData(IdentifiedObject $modelUnit, ArgumentParser $data): void
	{
		/** @var ModelUnit $modelUnit */
		parent::setData($modelUnit, $data);
		!$data->hasKey('baseUnitId') ?: $modelUnit->setSymbol($data->getInt('baseUnitId'));
		!$data->hasKey('symbol') ?: $modelUnit->setSymbol($data->getString('symbol'));
		!$data->hasKey('exponent') ?: $modelUnit->setExponent($data->getExponent('exponent'));
		!$data->hasKey('multiplier') ?: $modelUnit->setMultiplier($data->getString('multiplier'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelUnit;
	}

	protected function checkInsertObject(IdentifiedObject $modelUnit): void
	{
		/** @var ModelUnit $modelUnit */
		if ($modelUnit->getMultiplier() == null)
			throw new MissingRequiredKeyException('multiplier');
		if ($modelUnit->getExponent() == null)
			throw new MissingRequiredKeyException('exponent');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$modelUnit = $this->getObject($args->getInt('id'));
		if (!$modelUnit->getReferencedBy()->isEmpty())
			throw new DependentResourcesBoundException('UnitDefinitions');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'baseUnitId' => new Assert\Type(['type' => 'integer']),
			'symbol' => new Assert\Type(['type' => 'string']),
			'exponent' => new Assert\Type(['type' => 'float']),
			'multiplier' => new Assert\Type(['type' => 'float']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelUnit';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelUnitRepository::Class;
	}

	protected function getSub($entity)
	{
		echo $entity;
	}
}
