<?php

namespace App\Controllers;

use App\Entity\{
	ModelCompartment,
	ModelUnit,
	ModelUnitDefinition,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelUnitDefinitionRepository
};
use App\Exceptions\
{
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
 * @method ModelUnitDefinition getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelUnitDefinitionController extends ParentedSBaseController
{

	/** @var ModelUnitDefinitionRepository */
	private $unitDefinitionRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->unitDefinitionRepository = $c->get(ModelUnitDefinitionRepository::class);
	}

    protected static function getAlias(): string
    {
        return 'u';
    }
	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'symbol'];
	}

	protected function getData(IdentifiedObject $unitDefinition): array
	{
		/** @var ModelUnitDefinition $unitDefinition */
		$sBaseData = parent::getData($unitDefinition);
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
		parent::setData($unitDefinition, $data);
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
		$validatorArray = parent::getValidatorArray();
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

	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['model-id', 'model'];
	}
}
