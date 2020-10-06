<?php

namespace App\Entity\Repositories;

use App\Entity\PhysicalQuantity;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class PhysicalQuantityRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\PhysicalQuantityRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(PhysicalQuantity::class);
	}

	public function get(int $id)
	{
		return $this->em->find(PhysicalQuantity::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(q)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('q.id, q.name');
        $query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);
		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(PhysicalQuantity::class, 'q');
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
		return $query;
	}
}
