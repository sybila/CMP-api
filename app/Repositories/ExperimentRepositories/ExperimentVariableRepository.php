<?php

namespace App\Entity\Repositories;

use App\Entity\Experiment;
use App\Entity\ExperimentValues;
use App\Entity\ExperimentVariable;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentVariableRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	private $em;

	/** @var \Doctrine\ORM\VariableRepository */
	private $repository;

	/** @var Experiment */
	private $experiment;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ExperimentVariable::class);
	}

	protected static function getParentClassName(): string
	{
		return Experiment::class;
	}

    protected static function alias(): string
    {
        return 'v';
    }

	public function get(int $id)
	{
		return $this->em->find(ExperimentVariable::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(v)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('v.id, v.name, v.code, v.type');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
        return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->experiment;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of variable must be ' . $className);
		$this->experiment = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ExperimentVariable::class, 'v')
			->where('v.experimentId = :experimentId')
			->setParameter('experimentId', $this->experiment->getId());
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
		return $query;
	}

    /**
     * @param Experiment $object
     */
    public function add($object): void
    {
    }

    public function remove($object): void
    {
    }
}
