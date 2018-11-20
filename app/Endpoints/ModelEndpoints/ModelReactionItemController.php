<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelReaction,
	ModelReactionItem,
	ModelSpecie,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelReactionItemRepository,
	Repositories\ModelSpecieRepository,
	Repositories\ModelReactionRepository,
	Repositories\ModelCompartmentRepository,
	Structure
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
use App\Helpers\Validators;
use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ReactionItemRepository $repository
 * @method Entity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
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
		return ['id'];
	}

	protected function getData(IdentifiedObject $reactionItem): array
	{
		/** @var ModelReactionItem $reactionItem */

		return [
			'id' => $reactionItem->getId(),
			'specieId' => $reactionItem->getSpecieId()->getId(),
			'reactionId' => $reactionItem->getReactionId()->getId(),
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

}

final class ReactionParentedReactionItemController extends ModelLocalParameterController
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
		if (!$reactionItem->getReactionId())
			$reactionItem->setReactionId($this->repository->getParent());
		if ($data->hasKey('specieId')) {
			$specie = $this->repository->getEntityManager()->find(ModelSpecie::class, $data->getInt('specieId'));
			if($specie === null) {
				throw new NonExistingObjectException($data->getInt('specieId'), 'specie');
			}
			$reactionItem->setSpecieId($specie);
		}
		if ($data->hasKey('name'))
			$reactionItem->setName($data->getString('name'));
		if ($data->hasKey('type'))
			$reactionItem->setType($data->getString('type'));
		if ($data->hasKey('value'))
			$reactionItem->setValue($data->getInt('value'));
		if ($data->hasKey('stoichiometry'))
			$reactionItem->setStochiometry($data->getInt('stoichiometry'));
		if ($data->hasKey('isGlobal'))
			$reactionItem->setIsGlobal($data->getInt('isGlobal'));
	}

	protected function checkInsertObject(IdentifiedObject $reactionItem): void
	{
		/** @var ModelReactionItem $reactionItem */
		if ($reactionItem->getReactionId() == NULL)
			throw new MissingRequiredKeyException('reactionId');
		if ($reactionItem->getSpecieId() == NULL)
			throw new MissingRequiredKeyException('specieId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('specieId'))
			throw new MissingRequiredKeyException('specieId');
		return new ModelReactionItem;
	}
}


final class SpecieParentedReactionItemController extends ModelLocalParameterController
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
		if (!$reactionItem->getSpecieId())
			$reactionItem->setSpecieId($this->repository->getParent());
		if ($data->hasKey('reactionId')) {
			$reaction = $this->repository->getEntityManager()->find(ModelReaction::class, $data->getInt('reactionId'));
			if($reaction === null) {
				throw new NonExistingObjectException($data->getInt('reactionId'), 'reaction');
			}
			$reactionItem->setReactionId($reaction);
		}
		if ($data->hasKey('name'))
			$reactionItem->setName($data->getString('name'));
		if ($data->hasKey('value'))
			$reactionItem->setValue($data->getInt('value'));
		if ($data->hasKey('stoichiometry'))
			$reactionItem->setStochiometry($data->getInt('stoichiometry'));
		if ($data->hasKey('isGlobal'))
			$reactionItem->setIsGlobal($data->getInt('isGlobal'));
	}

	protected function checkInsertObject(IdentifiedObject $reactionItem): void
	{
		/** @var ModelReactionItem $reactionItem */
		if ($reactionItem->getReactionId() == NULL)
			throw new MissingRequiredKeyException('reactionId');
		if ($reactionItem->getSpecieId() == NULL)
			throw new MissingRequiredKeyException('specieId');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('reactionId'))
			throw new MissingRequiredKeyException('reactionId');
		return new ModelReactionItem;
	}
}
