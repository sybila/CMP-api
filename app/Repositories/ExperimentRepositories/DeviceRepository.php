<?php

namespace App\Entity\Repositories;

use App\Entity\Device;
use App\Entity\Experiment;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class DeviceRepository implements IEndpointRepository
{
    use QueryRepositoryHelper;

    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\DeviceRepository */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(Device::class);
    }

    public function get(int $id)
    {
        return $this->em->find(Device::class, $id);
    }

    protected static function alias(): string
    {
        return 'd';
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
            ->select('d.id, d.name, d.type, d.annotation');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(Device::class, 'd');
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
        return $query;
    }

}
