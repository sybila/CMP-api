<?php

namespace App\Entity\Repositories;

use App\Entity\ModelReaction;
use App\Entity\ModelSpecie;
use App\Entity\ModelReactionItem;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelReactionItemRepository implements IDependentEndpointRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ModelReactionItemRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelReactionItem::class);
	}

	protected static function getParentClassName(): array
	{
		return [ModelReaction::class, ModelSpecie::class];
	}

	public function getParent()
	{
		return $this->object;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelReactionItem::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(r)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('r.id, r.sbmlId, r.name, r.type, r.value, r.stoichiometry, r.isGlobal');

		return $query->getQuery()->getArrayResult();
	}

	public function setParent(IdentifiedObject $object): void
	{
		$classNames = static::getParentClassName();
		$errorString = '';
		$index = 0;
		foreach ($classNames as $className) {
			if ($object instanceof $className) {
				$this->object = $object;
				return;
			}
			$index == 0 ?: $errorString .= ' or ';
			$index++;
			$errorString .= $className;
		}
		throw new \Exception('Parent of reaction item must be ' . $errorString);
	}

	public function getEntityManager()
	{
		return $this->em;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = null;
		if ($this->object instanceof ModelSpecie) {
			$query = $this->em->createQueryBuilder()
				->from(ModelReactionItem::class, 'r')
				->where('r.specieId = :specieId')
				->setParameter('specieId', $this->object->getId());
		}
		if ($this->object instanceof ModelReaction) {
			$query = $this->em->createQueryBuilder()
				->from(ModelReactionItem::class, 'r')
				->where('r.reactionId = :reactionId')
				->setParameter('reactionId', $this->object->getId());
		}
		return $query;
	}

	public function add($object): void
	{
		// TODO: Implement add() method.
	}

	public function remove($object): void
	{
		// TODO: Implement remove() method.
	}
}
