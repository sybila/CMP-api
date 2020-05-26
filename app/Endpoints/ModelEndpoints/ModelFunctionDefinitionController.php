<?php

namespace App\Controllers;

use App\Entity\{
	ModelFunctionDefinition,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelFunctionDefinitionRepository
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
 * @property-read ModelFunctionDefinitionRepository $repository
 * @method ModelFunctionDefinition getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelFunctionDefinitionController extends ParentedSBaseController
{
	/** @var ModelFunctionDefinitionRepository */
	private $functionDefinitionRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->functionDefinitionRepository = $c->get(ModelFunctionDefinitionRepository::class);
	}

	protected static function getAlias(): string
    {
        return 'f';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $functionDefinition): array
	{
		/** @var ModelFunctionDefinition $functionDefinition */
		$sBaseData = parent::getData($functionDefinition);
		return array_merge($sBaseData, [
			'formula' => $functionDefinition->getFormula(),
		]);
	}

	protected function setData(IdentifiedObject $functionDefinition, ArgumentParser $data): void
	{
		/** @var ModelFunctionDefinition $functionDefinition */
		parent::setData($functionDefinition, $data);
		$functionDefinition->getModelId() ?: $functionDefinition->setModelId($this->repository->getParent());
		!$data->hasKey('formula') ?: $functionDefinition->setFormula($data->getString('formula'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelFunctionDefinition();
	}

	protected function checkInsertObject(IdentifiedObject $functionDefinition): void
	{
		/** @var ModelFunctionDefinition $functionDefinition */
		if ($functionDefinition->getModelId() == null)
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
			'formula' => new Assert\Type(['type' => 'string'])
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelFunctionDefinition';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelFunctionDefinitionRepository::Class;
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
