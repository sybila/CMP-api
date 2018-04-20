<?php

namespace App\Entity\Repositories;

use App\Entity\Rule;
use App\Entity\RuleStatus;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

interface RuleRepository extends IRepository
{
}

class RuleRepositoryImpl implements RuleRepository
{
	/** @var EntityManager */
	private $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Rule::class);
	}

	private function buildListQuery(?string $type): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()->from(Rule::class, 'r');
		return $query;
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter['type'] ?? null)
			->select('r.id, r.name, r.equation, r.code, r.modifier, r.status');

		foreach ($sort as $by => $how)
			$query->addOrderBy('r.' . $by, $how ?: null);

		if ($limit['limit'] > 0)
		{
			$query->setMaxResults($limit['limit'])
				->setFirstResult($limit['offset']);
		}

		return array_map(function(array $rule) {
			$rule['status'] = (string)RuleStatus::fromInt($rule['status'] ?? 1);
			return $rule;
		}, $query->getQuery()->getArrayResult());
	}

	public function getNumResults(array $filter): int
	{
		return (int)$this->buildListQuery($filter['type'] ?? null)
			->select('COUNT(r)')
			->getQuery()
			->getScalarResult()[0][1];
	}

	public function get(int $id): ?Rule
	{
		return $this->em->find(Rule::class, $id);
	}
}

