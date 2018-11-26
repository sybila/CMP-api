<?php

namespace App\Controllers;

use App\Entity\
{
	ModelUnitToDefinition,
	IdentifiedObject,
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
final class ModelReactionController extends ParentedRepositoryController
{
	/** @var ModelReactionRepository */
	private $reactionRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->reactionRepository = $c->get(ModelReactionRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $reaction): array
	{
		/** @var ModelReaction $reaction */
		return [
			'id' => $reaction->getId(),
			'modelId' => $reaction->getModelId()->getId(),
			'compartmentId' => $reaction->getCompartmentId() ? $reaction->getCompartmentId()->getId() : null,
			'name' => $reaction->getName(),
			'sbmlId' => $reaction->getSbmlId(),
			'isReversible' => $reaction->getIsReversible(),
			'isFast' => $reaction->getIsFast(),
			'rate' => $reaction->getRate(),
			'reactionItems' => $reaction->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'functions' => $reaction->getFunctions()->map(function (ModelFunction $function) {
				return ['id' => $function->getId(), 'name' => $function->getName()];
			})->toArray(),
		];
	}

	protected function setData(IdentifiedObject $reaction, ArgumentParser $data): void
	{
		/** @var Reaction $reaction */
		$reaction->getModelId() ?: $reaction->setModelId($this->repository->getParent()->getId());
		!$data->hasKey('compartmentId') ?: $reaction->setCompartmentId($data->getString('compartmentId'));
		!$data->hasKey('name') ?: $reaction->setName($data->getString('name'));
		!$data->hasKey('isReversible') ?: $reaction->setIsReversible($data->getInt('isReversible'));
		!$data->hasKey('isFast') ?: $reaction->setIsFast($data->getInt('isFast'));
		!$data->hasKey('rate') ?: $reaction->setRate($data->getString('rate'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('isReversible'))
			throw new MissingRequiredKeyException('isReversible');
		if (!$body->hasKey('isFast'))
			throw new MissingRequiredKeyException('isFast');
		return new ModelReaction;
	}

	protected function checkInsertObject(IdentifiedObject $reaction): void
	{
		/** @var ModelReaction $reaction */
		if ($reaction->getModelId() == null)
			throw new MissingRequiredKeyException('modelId');
		if ($reaction->isReversible() == null)
			throw new MissingRequiredKeyException('isReversible');
		if ($reaction->isFast() == null)
			throw new MissingRequiredKeyException('isFast');
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
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
			'isFast' => new Assert\Type(['type' => 'integer']),
			'isReversible' => new Assert\Type(['type' => 'integer']),
			'rate' => new Assert\Type(['type' => 'string']),
		]);
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
