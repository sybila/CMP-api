<?php

namespace App\Entity\Repositories;
use App\Entity\ExperimentEventType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentEventTypeRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ExperimentEventTypeRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ExperimentEventType::class);
	}

	public function get(int $id)
	{
		return $this->em->find(ExperimentEventType::class, $id);
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
			->from(ExperimentEventType::class, 'e');
		return $query;
	}

}
