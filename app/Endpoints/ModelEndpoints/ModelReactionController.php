<?php

namespace App\Controllers;

use App\Entity\
{
	IdentifiedObject,
	ModelParameter,
	ModelReaction,
	ModelReactionItem,
	ModelFunction,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelReactionRepository
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
 * @property-read ModelReactionRepository $repository
 * @method ModelReaction getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelReactionController extends ParentedSBaseController
{
	/** @var ModelReactionRepository */
	private $reactionRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->reactionRepository = $c->get(ModelReactionRepository::class);
	}

    protected static function getAlias(): string
    {
        return 'r';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $reaction): array
	{
		/** @var ModelReaction $reaction */
		$sBaseData = parent::getData($reaction);
		return array_merge($sBaseData, [
			'modelId' => $reaction->getModelId()->getId(),
			'compartmentId' => $reaction->getCompartmentId() ? $reaction->getCompartmentId()->getId() : null,
			'isReversible' => $reaction->getIsReversible(),
			'rate' => $reaction->getRate(),
			'reactionItems' => $reaction->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'functions' => $reaction->getFunctions()->map(function (ModelFunction $function) {
				return ['id' => $function->getId(), 'name' => $function->getName()];
			})->toArray(),
			'parameters' => $reaction->getParameters()->map(function (ModelParameter $parameter) {
				return ['id' => $parameter->getId(), 'name' => $parameter->getName()];
			})->toArray()
		]);
	}

	protected function setData(IdentifiedObject $reaction, ArgumentParser $data): void
	{
		/** @var Reaction $reaction */
		parent::setData($reaction, $data);
		$reaction->getModelId() ?: $reaction->setModelId($this->repository->getParent());
		!$data->hasKey('compartmentId') ?: $reaction->setCompartmentId($data->getString('compartmentId'));
		!$data->hasKey('isReversible') ?: $reaction->setIsReversible($data->getInt('isReversible'));
		!$data->hasKey('rate') ?: $reaction->setRate($data->getString('rate'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('isReversible'))
			throw new MissingRequiredKeyException('isReversible');
		return new ModelReaction;
	}

	protected function checkInsertObject(IdentifiedObject $reaction): void
	{
		/** @var ModelReaction $reaction */
		if ($reaction->getModelId() === null)
			throw new MissingRequiredKeyException('modelId');
		if ($reaction->getIsReversible() === null)
			throw new MissingRequiredKeyException('isReversible');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$specie = $this->getObject($args->getInt('id'));
		if (!$specie->getReactionItems()->isEmpty())
			throw new DependentResourcesBoundException('reactionItem');
		if (!$specie->getFunctions()->isEmpty())
			throw new DependentResourcesBoundException('function');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'isReversible' => new Assert\Type(['type' => 'integer']),
			'rate' => new Assert\Type(['type' => 'string']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'reaction';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelReactionRepository::Class;
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
