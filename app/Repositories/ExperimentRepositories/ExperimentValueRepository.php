<?php

namespace App\Entity\Repositories;

use App\Entity\ExperimentVariable;
use App\Entity\ExperimentValues;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentValueRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ValueRepository */
	private $repository;

	/** @var ExperimentVariable */
	private $variable;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(ExperimentValues::class);
    }

	protected static function getParentClassName(): string
	{
		return ExperimentVariable::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ExperimentValues::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(s)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('s.id, s.time, s.value');
		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->variable;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of value must be ' . $className);
		$this->variable = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ExperimentValues::class, 's')
			->where('s.variableId = :variableId')
			->setParameters([
				'variableId' => $this->variable->getId()
			]);
		return $query;
	}

}
