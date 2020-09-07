<?php

namespace App\Entity\Repositories;

use App\Entity\Experiment;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ExperimentRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Experiment::class);
	}

	public function get(int $id)
	{
		return $this->em->find(Experiment::class, $id);
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
			->select('e.id, e.name, e.description, e.protocol, e.started, e.inserted, e.status');
        $query = QueryRepositoryHelper::addFilterPaginationSortDql($query, $filter, $sort, $limit);
		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Experiment::class, 'e');
		return $query;
	}

	public function remove($object){
	    $this->em->remove($object);
	    $this->em->flush();
    }
}
