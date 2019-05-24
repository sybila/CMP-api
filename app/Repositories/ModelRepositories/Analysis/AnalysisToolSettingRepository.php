<?php

namespace App\Entity\Repositories;

use App\Entity\AnalysisTool;
use App\Entity\AnalysisToolSetting;
use App\Entity\ModelReaction;
use App\Entity\ModelSpecie;
use App\Entity\ModelReactionItem;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class AnalysisToolSettingRepository implements IDependentSBaseRepository
{
    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\AnalysisToolSettingRepository */
    private $repository;

    /** @var IdentifiedObject */
    private $parent;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(ModelReactionItem::class);
    }

    protected static function getParentClassName(): array
    {
        return [AnalysisTool::class];
    }

    public function getParent(): IdentifiedObject
    {
        return $this->parent;
    }

    public function get(int $id)
    {
        return $this->em->find(AnalysisToolSetting::class, $id);
    }

    public function getNumResults(array $filter): int
    {
        return ((int)$this->buildListQuery($filter)
            ->select('COUNT(r)')
            ->getQuery()
            ->getScalarResult());
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('s.id, s.name, s.sbmlId, s.sboTerm, s.notes, s.annotation, s.type, s.value, s.stoichiometry');

        return $query->getQuery()->getArrayResult();
    }

    public function setParent(IdentifiedObject $object): void
    {
        $classNames = static::getParentClassName();
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
        throw new \Exception('Parent of analysis task must be ' . $errorString);
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = null;
        if ($this->parent instanceof ModelSpecie) {
            $query = $this->em->createQueryBuilder()
                ->from(AnalysisToolSetting::class, 's')
                ->where('s.taskId = :taskId')
                ->setParameter('taskId', $this->parent->getId());
        }
        return $query;
    }

}
