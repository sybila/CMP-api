<?php

namespace App\Entity\Repositories;

use App\Entity\Organism;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

interface OrganismRepository extends PageableRepository
{
	public function get(int $id): ?Organism;
	public function getList(array $filter, ?array $sort, array $limit): array;
}

class OrganismRepositoryImpl implements OrganismRepository
{
	/** @var EntityManager */
	private $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Organism::class);
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()->from(Organism::class, 'o');
		return $query;
	}

	public function getList(array $filter, ?array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('o.id, o.name, o.code');

		if ($sort)
			foreach ($sort as $by => $how)
				$query->orderBy('o.' . $by, $how ?: null);

		if ($limit['limit'] > 0)
		{
			$query->setMaxResults($limit['limit'])
				->setFirstResult($limit['offset']);
		}

		return $query->getQuery()->getArrayResult();
	}

	public function getNumResults(array $filter): int
	{
		return (int)$this->buildListQuery($filter)
			->select('COUNT(o)')
			->getQuery()
			->getScalarResult()[0][1];
	}

	public function get(int $id): ?Organism
	{
		return $this->em->find(Organism::class, $id);
	}
}

