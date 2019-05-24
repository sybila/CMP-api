<?php


namespace App\Entity\Repositories;


use App\Entity\AnalysisTool;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class AnalysisToolRepository implements IEndpointRepository
{
    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\AnalysisToolRepository */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(AnalysisTool::class);
    }

    public function get(int $id)
    {
        return $this->em->find(AnalysisTool::class, $id);
    }

    public function getNumResults(array $filter): int
    {
        return ((int)$this->buildListQuery($filter)
            ->select('COUNT(t)')
            ->getQuery()
            ->getScalarResult());
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('t.id, t.name, t.cmd, t.vizId, t.location');
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(AnalysisTool::class, 't');
        return $query;
    }

}