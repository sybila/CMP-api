<?php

namespace App\Entity\Repositories;

use App\Entity\ModelUnit;
use App\Entity\ModelUnitDefinition;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


class ModelUnitRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ModelUnitRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelUnit::class);
	}

	public function get(int $id)
	{
		return $this->em->find(ModelUnit::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(u)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('u.id, (u.baseUnitId) as baseUnitId, u.name, u.symbol, u.exponent, u.multiplier');

		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelUnit::class, 'u');


		return $query;
	}


}
