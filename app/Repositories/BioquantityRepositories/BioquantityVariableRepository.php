<?php

namespace App\Entity\Repositories;

use App\Entity\BioquantityMethod;
use App\Entity\BioquantityVariable;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class BioquantityVariableRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\VariableRepository */
	private $repository;

	/** @var BioquantityMethod */
	private $method;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(BioquantityMethod::class);
    }

	protected static function getParentClassName(): string
	{
		return BioquantityMethod::class;
	}

	public function get(int $id)
	{
		return $this->em->find(BioquantityVariable::class, $id);
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
			->select('s.id','s.name, s.value, s.timeFrom, s.timeTo, s.source');
        $query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);
		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->method;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of variable must be ' . $className);
		$this->method = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(BioquantityVariable::class, 's')
			->where('s.methodId = :methodId')
			->setParameters([
				'methodId' => $this->method->getId()
			]);
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
		return $query;
	}

    public function add($object): void
    {
    }

    public function remove($object): void
    {
    }
}
