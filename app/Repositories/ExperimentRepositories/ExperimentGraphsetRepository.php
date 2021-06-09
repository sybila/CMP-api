<?php


namespace App\Entity\Repositories;


use App\Entity\Experiment;
use App\Entity\ExperimentGraphset;
use App\Entity\IdentifiedObject;
use App\Exceptions\WrongParentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\UnexpectedResultException;
use Exception;

class ExperimentGraphsetRepository implements IDependentEndpointRepository
{

    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    /** @var Experiment */
    private $experiment;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(ExperimentGraphset::class);
    }

    public function getParent(): IdentifiedObject
    {
        return $this->experiment;
    }

    protected static function getParentClassName(): string
    {
        return Experiment::class;
    }

    public function setParent(IdentifiedObject $object): void
    {
        $className = self::getParentClassName();
        if (!($object instanceof $className))
            throw new Exception('Parent of initial assignment must be ' . $className);
        $this->experiment = $object;
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
        return $this->em->find(ExperimentGraphset::class, $id);
    }

    public function getNumResults(array $filter): int
    {
        // TODO: Implement getNumResults() method.
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        // TODO: Implement getList() method.
    }

    public function canList(?int $role, ?int $id): bool
    {
        return true;
    }

    public function canDetail(?int $role, ?int $id): bool
    {
        return true;
    }
}