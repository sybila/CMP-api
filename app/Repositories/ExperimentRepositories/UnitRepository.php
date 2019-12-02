<?php

namespace App\Entity\Repositories;
use App\Entity\Unit;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class UnitRepository implements IEndpointRepository
{

    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\UnitRepository */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(Unit::class);
    }

    public function get(int $id)
    {
        return $this->em->find(Unit::class, $id);
    }

    public function getNumResults(array $filter): int
    {
        return ((int)$this->buildListQuery($filter)
            ->select('COUNT(u)')
            ->getQuery()
            ->getScalarResult());
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('u.id, u.name, u.code');
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(Unit::class, 'u');
        return $query;
    }

}
