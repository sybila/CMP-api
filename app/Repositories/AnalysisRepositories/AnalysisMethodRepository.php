<?php


namespace App\Entity\Repositories;


use App\Entity\AnalysisMethod;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class AnalysisMethodRepository implements IEndpointRepository
{
    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(AnalysisMethod::class);
    }

    public function get(int $id)
    {
        return $this->em->find(AnalysisMethod::class, $id);
    }

    public function getNumResults(array $filter): int
    {
        return $this->buildListQuery($filter)
            ->select('COUNT(am)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('am.id, am.name, am.description, am.annotation');
        $query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);

        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(AnalysisMethod::class, 'am');
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
        return $query;
    }
}