<?php

namespace App\Entity\Repositories;

use App\Entity\Classification;
use App\Entity\EntityClassification;
use App\Entity\RuleClassification;
use App\Exceptions\InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

interface ClassificationRepository extends PageableRepository
{
	public function get(int $id): Classification;
	public function getList(array $filter, ?array $sort, array $limit): array;
}

class ClassificationRepositoryImpl implements ClassificationRepository
{
	/** @var EntityManager */
	private $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Classification::class);
	}

	private function buildListQuery(?string $type): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Classification::class, 'c');

		if ($type)
		{
			if ($type == 'entity')
				$query->where('c INSTANCE OF ' . EntityClassification::class);
			elseif ($type == 'rule')
				$query->where('c INSTANCE OF ' . RuleClassification::class);
			else
				throw new InvalidArgumentException('type', $type);
		}

		return $query;
	}

	public function getList(array $filter, ?array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter['type'] ?? null)
			->select('c.id, c.name, TYPE(c) as type');

		if ($sort)
			foreach ($sort as $by => $how)
				$query->orderBy('c.' . $by, $how ?: null);

		if ($limit['limit'] > 0)
		{
			$query->setMaxResults($limit['limit'])
				->setFirstResult($limit['offset']);
		}

		return $query->getQuery()->getArrayResult();
	}

	public function getNumResults(array $filter): int
	{
		return (int)$this->buildListQuery($filter['type'] ?? null)
			->select('COUNT(c)')
			->getQuery()
			->getScalarResult()[0][1];
	}

	public function get(int $id): Classification
	{
		return $this->em->find(Classification::class, $id);
	}
}

