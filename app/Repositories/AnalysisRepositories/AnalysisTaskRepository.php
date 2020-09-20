<?php


namespace App\Entity\Repositories;


use App\Entity\AnalysisTask;
use App\Entity\Experiment;
use App\Entity\IdentifiedObject;
use App\Entity\Model;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;

class AnalysisTaskRepository implements IDependentEndpointRepository
{
    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    /** @var Model|Experiment */
    private $parentObject;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(AnalysisTask::class);
    }

    public function get(int $id)
    {
        return $this->em->find(AnalysisTask::class, $id);
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
            ->select('at.id, at.name, at.description, at.annotation, 
            at.userId, at.objectType, at.objectId, IDENTITY(at.method) as methodId'
            );
        $query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(AnalysisTask::class, 'at')
            ->where('at.objectId = :objectId')
            ->andWhere('at.objectType = :type')
            ->setParameters(new ArrayCollection([
                new Parameter('objectId', $this->parentObject->getId()),
                new Parameter('type',$this->getParentType())
            ]));
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
        return $query;
    }

    public function getParentType(): string
    {
        $type = '';
        if ($this->parentObject instanceOf Model)
            $type = 'model';
        if ($this->parentObject instanceOf Experiment)
            $type = 'experiment';
        return $type;
    }

    public function setParent(IdentifiedObject $object): void
    {
        $this->parentObject = $object;
    }

    public function getParent(): IdentifiedObject
    {
        return $this->parentObject;
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