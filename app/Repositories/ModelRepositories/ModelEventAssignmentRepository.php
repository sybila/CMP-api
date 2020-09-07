<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelEvent;
use App\Entity\ModelEventAssignment;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelEventAssignmentRepository implements IDependentSBaseRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EventAssignmentRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelEventAssignment::class);
	}

	protected static function getParentClassName(): string
	{
		return ModelEvent::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelEventAssignment::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(e)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('e.id, e.name, e.sbmlId, e.sboTerm, e.notes, e.annotation, e.formula');
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
			throw new \Exception('Parent of event assignment must be ' . $className);
		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelEventAssignment::class, 'e')
			->where('e.eventId = :eventId')
			->setParameter('eventId', $this->object->getId());
        $query = QueryRepositoryHelper::addFilterDql($query, $filter);
		return $query;
	}

}
