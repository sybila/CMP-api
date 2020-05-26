<?php

namespace App\Controllers;

use App\Entity\{
	ModelRule,
	ModelReaction,
	ModelReactionItem,
	ModelParameter,
	IdentifiedObject,
	Repositories\ModelRepository,
	Repositories\IEndpointRepository,
	Repositories\ModelParameterRepository,
	Repositories\ModelReactionRepository
};
use App\Exceptions\
{
	CompartmentLocationException,
	InvalidArgumentException,
	MissingRequiredKeyException,
	NonExistingObjectException,
	UniqueKeyViolationException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelParameterRepository $repository
 * @method ModelParameter getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class ModelParameterController extends ParentedSBaseController
{
	/** @var ModelParameterRepository */
	private $parameterRepository;

	/** @var EntityManager * */
	protected $em;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->parameterRepository = $c->get(ModelParameterRepository::class);
	}

    protected static function getAlias(): string
    {
        return 'p';
    }

	protected static function getAllowedSort(): array
	{
		return ['id, name'];
	}

	public function readSbmlId(Request $request, Response $response, ArgumentParser $args)
	{
		$parameter = $this->repository->getBySbmlId($args->getString('sbmlId'));
		return self::formatOk(
			$response,
			$parameter ? $this->getData($parameter) : null
		);
	}

	protected function getData(IdentifiedObject $parameter): array
	{
		/** @var ModelParameter $parameter */
		$sBaseData = parent::getData($parameter);
		return array_merge($sBaseData, [
			'value' => $parameter->getValue(),
			'isConstant' => $parameter->getValue(),
			'reactionItems' => $parameter->getReactionsItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'rules' => $parameter->getRules()->map(function (ModelRule $rule) {
				return ['id' => $rule->getId(), 'equation' => $rule->getEquation()];
			})->toArray()
		]);
	}

	protected function setData(IdentifiedObject $parameter, ArgumentParser $data): void
	{
		/** @var ModelParameter $parameter */
		parent::setData($parameter, $data);
		!$data->hasKey('value') ?: $parameter->setValue($data->getString('value'));
		!$data->hasKey('isConstant') ?: $parameter->setIsConstant($data->getString('isConstant'));
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'value' => new Assert\Type(['type' => 'float']),
			'isConstant' => new Assert\Type(['type' => 'integer']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'parameter';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelParameterRepository::class;
	}

}

final class ModelParentedParameterController extends ModelParameterController
{

	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['model-id', 'model'];
	}

	protected function setData(IdentifiedObject $parameter, ArgumentParser $data): void
	{
		/** @var ModelParameter $parameter */
		$parameter->getModelId() ?: $parameter->setModelId($this->repository->getParent());
		if ($data->hasKey('reactionId')) {
			$reaction = $this->repository->getEntityManager()->find(ModelReaction::class, $data->getInt('reactionId'));
			if ($reaction === null) {
				throw new NonExistingObjectException($data->getInt('reactionId'), 'reaction');
			}
			$parameter->setReactionId($reaction);
		}
		parent::setData($parameter, $data);
	}

	protected function checkInsertObject(IdentifiedObject $parameter): void
	{
		/** @var ModelParameter $parameter */
		if ($parameter->getSbmlId() === null)
			throw new MissingRequiredKeyException('sbmlId');
		if ($parameter->getIsConstant() === null)
			throw new MissingRequiredKeyException('isConstant');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('isConstant'))
			throw new MissingRequiredKeyException('isConstant');
		if (!$body->hasKey('sbmlId'))
			throw new MissingRequiredKeyException('sbmlId');
		return new ModelParameter;
	}
}

final class ReactionItemParentedParameterController extends ModelParameterController
{

	protected static function getParentRepositoryClassName(): string
	{
		return ModelReactionRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['reactionItem-id', 'reactionItem'];
	}

	protected function checkInsertObject(IdentifiedObject $parameter): void
	{
		/** @var ModelParameter $parameter */
		if ($parameter->getSbmlId() === null)
			throw new MissingRequiredKeyException('sbmlId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('sbmlId'))
			throw new MissingRequiredKeyException('sbmlId');
		return new ModelParameter;
	}
}
