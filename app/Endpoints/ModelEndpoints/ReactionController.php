<?php

namespace App\Controllers;

use App\Entity\
{
	Entity,
	ModelCompartment,
	IdentifiedObject,
	ModelReaction,
	ModelReactionItem,
	ModelFunction,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository,
	Repositories\ModelReactionItemRepository,
	Repositories\ReactionRepository,
	Repositories\CompartmentRepository,
	Structure
};
use App\Exceptions\
{
	CompartmentLocationException,
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
 * @property-read ReactionRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ReactionController extends ParentedRepositoryController
{

	/** @var ReactionRepository */
	private $reactionRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->reactionRepository = $c->get(ReactionRepository::class);
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
			'modelId' => $reaction->getModelId(),
			'compartmentId' => $reaction->getCompartmentId(),
			'name' => $reaction->getName(),
			'equartion' => $reaction->getRate(),
			'isReversible' => $reaction->getIsReversible(),
			'isFast' => $reaction->getIsFast(),
			'rate' => $reaction->getRate(),
			'reactionItems' => $reaction->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
				return ['id' => $reactionItem->getId(), 'name' => $reactionItem->getName()];
			})->toArray(),
			'functions' => $reaction->getFunctions()->map(function (ModelFunction $functions) {
				return ['id' => $functions->getId()];
			})->toArray(),
		];
	}

	protected function setData(IdentifiedObject $reaction, ArgumentParser $data): void
	{
		/** @var Reaction $reaction */
		if(!$reaction->getModelId())
			$reaction->setModelId($this->repository->getParent()->getId());
		if ($data->hasKey('compartmentId'))
			$reaction->setCompartmentId($data->getString('compartmentId'));
		if ($data->hasKey('name'))
			$reaction->setName($data->getString('name'));
		if ($data->hasKey('isReversible'))
			$reaction->setIsReversible($data->getInt('isReversible'));
		if ($data->hasKey('isFast'))
			$reaction->setIsFast($data->getInt('isFast'));
		if ($data->hasKey('rate'))
			$reaction->setRate($data->getString('rate'));


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
		if ($reaction->getModelId() == NULL)
			throw new MissingRequiredKeyException('modelId');
		if ($reaction->isReversible() == NULL)
			throw new MissingRequiredKeyException('isReversible');
		if ($reaction->isFast() == NULL)
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
		]);
	}

	protected static function getObjectName(): string
	{
		return 'reaction';
	}

	protected static function getRepositoryClassName(): string
	{
		return ReactionRepository::Class;
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
