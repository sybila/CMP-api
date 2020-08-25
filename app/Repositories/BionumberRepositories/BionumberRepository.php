<?php

declare(strict_types=1);

namespace App\Entity\Repositories;

use App\Entity\Bionumber;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


/**
 * @author Alexandra Stanová stanovaalex@mail.muni.cz
 */
class BionumberRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\BionumberRepository */
	private $repository;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Bionumber::class);
	}


	public function get(int $id)
	{
		return $this->em->find(Bionumber::class, $id);
	}


	public function getList(array $filter, array $sort, array $limit): array
	{
        $query = $this->buildListQuery($filter)
            ->select('b.id, b.name, b.organismId, b.userId, b.isValid, b.value, b.link, b.timeFrom, b.timeTo, b.valueFrom, b.valueTo, b.valueStep');
        $query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);
        return $query->getQuery()->getArrayResult();
	}


	public function getNumResults(array $filter): int
	{
		return ((int) $this->buildListQuery($filter)
				->select('COUNT(b)')
				->getQuery()
				->getScalarResult());
	}


	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Bionumber::class, 'b');
		$query = QueryRepositoryHelper::addFilterDql($query, $filter);
		return $query;
	}

}
