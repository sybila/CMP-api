<?php

namespace App\Entity\Repositories;

use App\Entity\ModelFunction;
use App\Entity\ModelReaction;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelFunctionRepository implements IDependentEndpointRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ModelFunction */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelFunction::class);
	}

	protected static function getParentClassName(): string
	{
		return ModelReaction::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelFunction::class, $id);
	}

	public function getParent()
	{
		return $this->object;
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(f)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('f.id, f.name, f.formula');
        $query = QueryRepositoryHelper::addFilterPaginationSortDql($query, $filter, $sort, $limit);

		return $query->getQuery()->getArrayResult();
	}

	public function setParent(IdentifiedObject $object): void
	{
		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelFunction::class, 'f')
			->where('f.reactionId = :reactionId')
			->setParameter('reactionId', $this->object->getId());
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
