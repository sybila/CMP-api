<?php

namespace App\Controllers;

use App\Entity\{
	ModelConstraint,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelConstraintRepository
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
 * @property-read ModelConstraintRepository $repository
 * @method Constraint getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelConstraintController extends ParentedSBaseController
{
	/** @varModelConstraintRepository */
	private $constraintRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->constraintRepository = $c->get(ModelConstraintRepository::class);
	}

    protected static function getAlias(): string
    {
        return 'c';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $constraint): array
	{
		/** @var ModelConstraint $constraint */
		$sBaseData = parent::getData($constraint);
		return array_merge($sBaseData, [
			'message' => $constraint->getMessage(),
			'formula' => $constraint->getFormula(),
		]);
	}

	protected function setData(IdentifiedObject $constraint, ArgumentParser $data): void
	{
		/** @var ModelConstraint $constraint */
		parent::setData($constraint, $data);
		$constraint->getModelId() ?: $constraint->setModelId($this->repository->getParent());
		!$data->hasKey('message') ?: $constraint->setMessage($data->getString('message'));
		!$data->hasKey('formula') ?: $constraint->setFormula($data->getString('formula'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new ModelConstraint;
	}

	protected function checkInsertObject(IdentifiedObject $constraint): void
	{
		/** @var ModelConstraint $constraint */
		if ($constraint->getModelId() == null)
			throw new MissingRequiredKeyException('modelId');
		if ($constraint->getFormula() == null)
			throw new MissingRequiredKeyException('formula');
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
			'message' => new Assert\Type(['type' => 'string']),
			'formula' => new Assert\Type(['type' => 'string'])
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelConstraint';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelConstraintRepository::Class;
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
