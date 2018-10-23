<?php

namespace App\Entity\Repositories;

use App\Entity\Atomic;
use App\Entity\AtomicState;
use App\Entity\Compartment;
use App\Entity\Complex;
use App\Entity\Model;
use App\Entity\ModelReaction;
use App\Entity\ModelSpecie;
use App\Entity\ModelReactionItem;
use App\Entity\EntityStatus;
use App\Entity\IdentifiedObject;
use Doctrine\Common\Collections\ArrayCollection;
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

	protected static function getParentClassName(): string
	{
		return ModelReaction::class;
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
			->select('r.id, (r.reactionId) as reactionId, (r.specieId) as specieId, r.value, r.isGlobal');

		return $query->getQuery()->getArrayResult();
	}


	public function setParent(IdentifiedObject $object): void
	{
		// TODO: Rework parent check for multi-parented repositories
		/*$className = static::getParentClassName();
		dump($className);exit;
		if (!($object instanceof $className))
			throw new \Exception('Parent of reaction must be ' . $className);*/

		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
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
		// TODO: Refactor
	}

	public function remove($object): void
	{
		// TODO: Refactor
	}
}
