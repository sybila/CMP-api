<?php

namespace App\Entity\Repositories;

use App\Entity\ModelChange;
use App\Entity\ModelReaction;
use App\Entity\ModelSpecie;
use App\Entity\ModelReactionItem;
use App\Entity\IdentifiedObject;
use App\Entity\ModelTask;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


class ModelChangeRepository implements IDependentSBaseRepository
{
    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\ModelChangeRepository */
    private $repository;

    /** @var IdentifiedObject */
    private $parent;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(ModelChange::class);
    }

    protected static function getParentClassName(): array
    {
        return [ModelTask::class];
    }

    public function getParent(): IdentifiedObject
    {
        return $this->parent;
    }

    public function get(int $id)
    {
        return $this->em->find(ModelChange::class, $id);
    }

    public function getNumResults(array $filter): int
    {
        return ((int)$this->buildListQuery($filter)
            ->select('COUNT(c)')
            ->getQuery()
            ->getScalarResult());
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('c.id, c.task_id, c.type, c.origin_id, c.value');

        return $query->getQuery()->getArrayResult();
    }

    public function setParent(IdentifiedObject $object): void
    {
        dump($object);
        $classNames = static::getParentClassName();
        dump($classNames);
        $errorString = '';
        $index = 0;
        foreach ($classNames as $className) {
            if ($object instanceof $className) {
                $this->parent = $object;
                return;
            }
            $index == 0 ?: $errorString .= ' or ';
            $index++;
            $errorString .= $className;
        }
        throw new \Exception('Parent of model change must be ' . $errorString);
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        dump($this->parent );
        if ($this->parent instanceof ModelTask) {
            $query = $this->em->createQueryBuilder()
                ->from(ModelChange::class, 'c')
                ->where('c.modelTaskId = :modelTaskId')
                ->setParameter('modelTaskId', $this->parent->getId());
        }
        return $query;
    }

}
