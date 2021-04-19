<?php

namespace App\Entity\Repositories;

use App\Entity\IdentifiedObject;
use App\Entity\Model;
use App\Entity\ModelDataset;
use App\Exceptions\WrongParentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;

class ModelDatasetRepository implements IDependentEndpointRepository
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
        $this->repository = $em->getRepository(ModelDataset::class);
    }

    public function getParent(): IdentifiedObject
    {
        return $this->model;
    }

    public function setParent(IdentifiedObject $object): void
    {
        $className = Model::class;
        if (!($object instanceof $className))
            throw new WrongParentException(get_class($object),null,'ModelDataset',null);
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

    public function get(int $id)
    {
        return $this->em->find(ModelDataset::class, $id);
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
            ->select('d.id, d.name');
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(ModelDataset::class, 'd')
            ->where('d.model = :model')
            ->setParameter('model', $this->getParent());
        return $query;
    }
}