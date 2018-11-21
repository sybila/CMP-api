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
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelUnitController extends WritableRepositoryController
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
		return [
			'id' => $modelUnit->getId(),
			'baseUnitId' => $modelUnit->getBaseUnitId(),
			'name' => $modelUnit->getName(),
			'symbol' => $modelUnit->getSymbol(),
			'exponent' => $modelUnit->getExponent(),
			'multiplier' => $modelUnit->getExponent()
			];
	}

	protected function setData(IdentifiedObject $modelUnit, ArgumentParser $data): void
	{
		/** @var ModelUnit $modelUnit */
		if ($data->hasKey('name'))
			$modelUnit->setName($data->getString('name'));

	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{

		return new ModelUnit;
	}

	protected function checkInsertObject(IdentifiedObject $modelUnit): void
	{
		/** @var ModelUnit $modelUnit */
		if ($modelUnit->getMultiplier() == NULL)
			throw new MissingRequiredKeyException('multiplier');
		if ($modelUnit->getExponent() == NULL)
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
		return new Assert\Collection([
			'baseUnitId' => new Assert\Type(['type' => 'integer']),
			'name' => new Assert\Type(['type' => 'string']),
			'symbol' => new Assert\Type(['type' => 'string']),
			'exponent' => new Assert\Type(['type' => 'float']),
			'multiplier' => new Assert\Type(['type' => 'float']),
		]);
	}


	protected static function getObjectName(): string
	{
		return 'modelUnit';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelUnitRepository::Class;
	}

	protected function getSub($entity) {
		echo $entity;
	}


}
