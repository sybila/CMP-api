<?php

namespace App\Entity\Repositories;
use App\Entity\ExperimentEventVarType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentEventVarTypeRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ExperimentEventVarTypeRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ExperimentEventVarType::class);
	}

	public function get(int $id)
	{
		return $this->em->find(ExperimentEventVarType::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(e)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('e.id, e.type');
		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ExperimentEventVarType::class, 'e');
		return $query;
	}

}
