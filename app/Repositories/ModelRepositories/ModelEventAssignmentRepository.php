<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelEvent;
use App\Entity\ModelEventAssignment;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


class ModelEventAssignmentRepository implements IDependentEndpointRepository
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
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('e.id, e.formula');

		return $query->getQuery()->getArrayResult();
	}

	public function getParent():IdentifiedObject {
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
		return $query;
	}


	public function add($object): void
	{
		// TODO: Refactor this method since its pointless in onetomany relationship
	}

	public function remove($object): void
	{
		// TODO: Refactor this method since its pointless in onetomany relationship
	}
}
