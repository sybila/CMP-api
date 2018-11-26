<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelCompartment;
use App\Entity\IdentifiedObject;
use App\Entity\ModelInitialAssignment;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


class ModelInitialAssignmentRepository implements IDependentEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\InitialAssignmentRepository */
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
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('i.id, i.formula');

		return $query->getQuery()->getArrayResult();
	}

	public function getParent():IdentifiedObject {
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
