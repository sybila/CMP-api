<?php


namespace App\Entity\Repositories;


use App\Entity\AnalysisMethod;
use App\Entity\AnalysisSettings;
use App\Entity\IdentifiedObject;
use App\Exceptions\WrongParentException;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class AnalysisSettingsRepository implements IDependentEndpointRepository
{

    use QueryRepositoryHelper;

    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    /** @var AnalysisMethod is a parent */
    private $method;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(AnalysisSettings::class);
    }


    public function get(int $id)
    {
        return $this->em->find(AnalysisSettings::class, $id);
    }

    protected static function alias(): string
    {
        return 's';
    }

    public function getNumResults(array $filter): int
    {
        return $this->buildListQuery($filter)
            ->select('COUNT(s)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('s.id, s.name, s.methodId');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(AnalysisSettings::class, 's')
            ->where('s.methodId = :methodId')
            ->setParameter('methodId', $this->method->getId());
        $query = $this->addFilterDql($query, $filter);
        return $query;
    }


    //PARENT STUFF

    public function getParent(): IdentifiedObject
    {
        return $this->method;
    }

    protected static function getParentClassName(): string
    {
        return AnalysisMethod::class;
    }

    public function setParent(IdentifiedObject $object): void
    {
        $className = static::getParentClassName();
        if (!($object instanceof $className))
            throw new WrongParentException($className, null, 'analysisSettings', null);
        $this->method = $object;
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