<?php


namespace App\Entity\Repositories;

use App\Entity\AnalysisDataset;
use App\Entity\IdentifiedObject;
use App\Entity\Model;
use App\Exceptions\WrongParentException;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class AnalysisDatasetRepository implements IDependentEndpointRepository
{
    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    /**@var Model */
    private $model;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(AnalysisDataset::class);
    }

    public function get(int $id)
    {
        return $this->em->find(AnalysisDataset::class, $id);
    }

    public function getNumResults(array $filter): int
    {
        return $this->buildListQuery($filter)
            ->select('COUNT(d)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('d.id, d.name, d.description, d.annotation');
        $query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(AnalysisDataset::class, 'd')
            ->where('d.modelId = :modelId')
            ->setParameter('modelId', $this->getParent()->getId());
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
        return $query;
    }

    public function getParent(): IdentifiedObject
    {
        return $this->model;
    }

    public function setParent(IdentifiedObject $object): void
    {
        $className = Model::class;
        if (!($object instanceof $className))
            throw new WrongParentException(get_class($object),null,'AnalysisDataset',null);
        $this->model = $object;
    }

    public function add($object): void
    {
        // TODO: Implement add() method.
    }

    public function remove($object): void
    {
        // TODO: Implement remove() method.
    }
}