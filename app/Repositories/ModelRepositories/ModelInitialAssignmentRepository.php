<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\IdentifiedObject;
use App\Entity\ModelInitialAssignment;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelInitialAssignmentRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ModelInitialAssignmentRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelInitialAssignment::class);
	}

	protected static function getParentClassName(): string
	{
		return Model::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelInitialAssignment::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(i)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('i.id, i.name, i.sbmlId, i.sboTerm, i.notes, i.annotation, i.formula');
        $query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);

		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->object;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of initial assignment must be ' . $className);
		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelInitialAssignment::class, 'i')
			->where('i.modelId = :modelId')
			->setParameter('modelId', $this->object->getId());
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
		return $query;
	}
}
