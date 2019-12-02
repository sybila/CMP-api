<?php

namespace App\Entity\Repositories;

use App\Entity\Protocol;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ProtocolRepository implements IEndpointRepository
{

    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\ProtocolRepository */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(Protocol::class);
    }

    public function get(int $id)
    {
        return $this->em->find(Protocol::class, $id);
    }

    public function getNumResults(array $filter): int
    {
        return ((int)$this->buildListQuery($filter)
            ->select('COUNT(d)')
            ->getQuery()
            ->getScalarResult());
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('d.id, d.inserted, d.protocol');
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(Protocol::class, 'd');
        return $query;
    }

}
