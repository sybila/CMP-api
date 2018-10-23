<?php

namespace App\Controllers;

use App\Entity\{
	Entity,
	ModelReaction,
	ModelReactionItem,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ModelReactionItemRepository,
	Repositories\SpecieRepository,
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
use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ReactionRepository $repository
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
			'name' => $reactionItem->getName(),
			'isGlobal' => $reactionItem->getIsGlobal(),
		];
	}

	protected function checkInsertObject(IdentifiedObject $object): void
	{
		//todo
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		try {
			$a = parent::delete($request, $response, $args);
		} catch (\Exception $e) {
			throw new InvalidArgumentException('annotation', $args->getString('annotation'), 'must be in format term:id');
		}
		return $a;

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

final class ReactionParentedReactionItemController extends ModelReactionItemController {

	protected static function getParentRepositoryClassName(): string
	{
		return ReactionRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['reaction-id', 'reaction'];
	}

	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelReactionItem $reactionItem */
		/*if(!$reactionItem->getReactionId())
			$reactionItem->setReactionId($this->repository->getParent()->getId());
		if ($data->hasKey('specieId'))
			$reactionItem->setSpecieId($data->getInt('specieId'));
		if ($data->hasKey('name'))
			$reactionItem->setName($data->getString('name'));
		if ($data->hasKey('value'))
			$reactionItem->setValue($data->getInt('value'));
		if ($data->hasKey('stochiometry'))
			$reactionItem->setStochiometry($data->getInt('stochiometry'));
		if ($data->hasKey('isGlobal'))
			$reactionItem->setIsGlobal($data->getInt('isGlobal'));*/
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('specieId'))
			throw new MissingRequiredKeyException('specieId');

		return new ModelReactionItem;
	}
}


final class SpecieParentedReactionItemController extends ModelReactionItemController {

	protected static function getParentRepositoryClassName(): string
	{
		return SpecieRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['specie-id', 'specie'];
	}

	protected function setData(IdentifiedObject $reactionItem, ArgumentParser $data): void
	{
		/** @var ModelReactionItem $reactionItem */
		/*if(!$reactionItem->getSpecieId())
			$reactionItem->setSpecieId($this->repository->getParent()->getId());
		if ($data->hasKey('reactionId'))
			$reaction = $this->em->find(ModelReaction::class, $data->getInt('reactionKey'));
			$reactionItem->setReactionId($reaction);
		if ($data->hasKey('name'))
			$reactionItem->setName($data->getString('name'));
		if ($data->hasKey('value'))
			$reactionItem->setValue($data->getInt('value'));
		if ($data->hasKey('stochiometry'))
			$reactionItem->setStochiometry($data->getInt('stochiometry'));
		if ($data->hasKey('isGlobal'))
			$reactionItem->setIsGlobal($data->getInt('isGlobal'));*/
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('reactionId'))
			throw new MissingRequiredKeyException('reactionId');

		return new ModelReactionItem;
	}
}
