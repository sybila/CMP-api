<?php

namespace App\Entity\Repositories;

use App\Entity\AnalysisType;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class AnalysisTypeRepository implements IEndpointRepository
{
    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(AnalysisType::class);
    }

    public function get(int $id)
    {
        return $this->em->find(AnalysisType::class, $id);
    }

    public function getNumResults(array $filter): int
    {
        return $this->buildListQuery($filter)
            ->select('COUNT(at)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('at.id, at.name, at.description, at.annotation');
        $query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);

        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(AnalysisType::class, 'at');
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
        return $query;
    }
}