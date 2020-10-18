<?php

namespace App\Entity\Repositories;

use App\Entity\ModelUnit;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

//FIXME so are THESE units DEPRECATED?
class ModelUnitRepository implements IEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
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

    protected static function alias(): string
    {
        return 'u';
    }

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(u)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('u.id, (u.baseUnitId) as baseUnitId, u.name, u.sbmlId, u.sboTerm, u.notes, u.annotation, u.symbol, u.exponent, u.multiplier');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelUnit::class, 'u');
        $query = $this->addFilterDql($query, $filter);
		return $query;
	}

}
