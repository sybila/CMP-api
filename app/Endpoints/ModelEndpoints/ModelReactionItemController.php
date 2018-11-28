<?php

namespace App\Controllers;

use App\Entity\{
	ModelParameter,
	ModelReaction,
	ModelReactionItem,
	ModelSpecie,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelReactionItemRepository,
	Repositories\ModelSpecieRepository,
	Repositories\ModelReactionRepository
};
use App\Exceptions\
{
	MissingRequiredKeyException,
	NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ReactionItemRepository $repository
 * @method ModelReactionItem getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
abstract class ModelReactionItemController extends ParentedRepositoryController
{
	/** @var ModelReactionItemRepository */
	private $reactionItemRepository;

	/** @var EntityManager * */
	protected $em;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->reactionItemRepository = $c->get(ModelReactionItemRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $reactionItem): array
	{
		/** @var ModelReactionItem $reactionItem */
		return [
			'id' => $reactionItem->getId(),
			'reactionId' => $reactionItem->getReactionId()->getId(),
			'specieId' => $reactionItem->getSpecieId() ? $reactionItem->getSpecieId()->getId() : null,
			'parameterId' => $reactionItem->getParameterId() ? $reactionItem->getParameterId()->getId() : null,
			'type' => $reactionItem->getType(),
			'name' => $reactionItem->getName(),
			'stoichiometry' => $reactionItem->getStoichiometry(),
			'isGlobal' => $reactionItem->getIsGlobal(),
		];
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
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
		return 'reactionItem';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelReactionItemRepository::class;
	}

	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		!$data->hasKey('name') ? $reactionItem->setName($data->getString('sbmlId')) : $reactionItem->setName($data->getString('name'));
		!$data->hasKey('sbmlId') ?: $reactionItem->setSbmlId($data->getString('sbmlId'));
		!$data->hasKey('type') ?: $reactionItem->setType($data->getString('type'));
		!$data->hasKey('value') ?: $reactionItem->setValue($data->getInt('value'));
		!$data->hasKey('stoichiometry') ?: $reactionItem->setStochiometry($data->getFloat('stoichiometry'));
		!$data->hasKey('isGlobal') ?: $reactionItem->setIsGlobal($data->getInt('isGlobal'));
	}

}

final class ReactionParentedReactionItemController extends ModelReactionItemController
{

	protected static function getParentRepositoryClassName(): string
	{
		return ModelReactionRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['reaction-id', 'reaction'];
	}

	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelReactionItem $reactionItem */
		$reactionItem->getReactionId() ?: $reactionItem->setReactionId($this->repository->getParent());
		if ($data->hasKey('specieId')) {
			if ($data->hasKey('parameterId')) {
				throw new Exception('reaction item cannot refer to specie and parameter at the same time');
			}
			$specie = $this->repository->getEntityManager()->find(ModelSpecie::class, $data->getInt('specieId'));
			if ($specie === null) {
				throw new NonExistingObjectException($data->getInt('specieId'), 'specie');
			}
			$reactionItem->setParameterId(null);
			$reactionItem->setSpecieId($specie);
		} else {
			$parameter = $this->repository->getEntityManager()->find(ModelParameter::class, $data->getInt('parameterId'));

			if ($parameter === null) {
				throw new NonExistingObjectException($data->getInt('parameterId'), 'parameter');
			}
			$reactionItem->setSpecieId(null);
			$reactionItem->setParameterId($parameter);
		}
		parent::setData($reactionItem, $data);
	}

	protected function checkInsertObject(IdentifiedObject $reactionItem): void
	{
		/** @var ModelReactionItem $reactionItem */
		if ($reactionItem->getReactionId() == null)
			throw new MissingRequiredKeyException('reactionId');
		if ($reactionItem->getSpecieId() == null && $reactionItem->getParameterId() === null)
			throw new MissingRequiredKeyException('specieId or parameterId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('specieId') && !$body->hasKey('parameterId'))
			throw new MissingRequiredKeyException('specieId or parameterId');
		return new ModelReactionItem;
	}
}

final class SpecieParentedReactionItemController extends ModelReactionItemController
{

	protected static function getParentRepositoryClassName(): string
	{
		return ModelSpecieRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['specie-id', 'specie'];
	}

	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelReactionItem $reactionItem */
		$reactionItem->getSpecieId() ?: $reactionItem->setSpecieId($this->repository->getParent());
		if ($data->hasKey('reactionId')) {
			$reaction = $this->repository->getEntityManager()->find(ModelReaction::class, $data->getInt('reactionId'));
			if ($reaction === null) {
				throw new NonExistingObjectException($data->getInt('reactionId'), 'reaction');
			}
			$reactionItem->setReactionId($reaction);
		}
		parent::setData($reactionItem, $data);
	}

	protected function checkInsertObject(IdentifiedObject $reactionItem): void
	{
		/** @var ModelReactionItem $reactionItem */
		if ($reactionItem->getReactionId() == null)
			throw new MissingRequiredKeyException('reactionId');
		if ($reactionItem->getSpecieId() == null)
			throw new MissingRequiredKeyException('specieId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('reactionId'))
			throw new MissingRequiredKeyException('reactionId');
		if ($body->hasKey('parameterId'))
			throw new Exception('reaction item cannot refer to specie and parameter at the same time');
		return new ModelReactionItem;
	}
}
