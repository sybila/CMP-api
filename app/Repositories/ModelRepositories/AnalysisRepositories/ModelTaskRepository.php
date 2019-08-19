<?php
namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelCompartment;
use App\Entity\IdentifiedObject;
use App\Entity\ModelTask;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelTaskRepository implements IEndpointRepository
{
    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\ModelTaskRepository */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(ModelTask::class);
    }

    public function get(int $id)
    {
        return $this->em->find(ModelTask::class, $id);
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
            ->select('t.id, t.name, t.notes, t.annotation, t.userId, t.modelId, t.analysisToolId, t.outputPath');
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter)
    {
        $query = $this->em->createQueryBuilder()
            ->from(ModelTask::class, 't');
        return $query;
    }
}