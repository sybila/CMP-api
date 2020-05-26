<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelUnitDefinition;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelUnitDefinitionRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\UnitDefinitionRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelUnitDefinition::class);
	}

	protected static function getParentClassName(): string
	{
		return Model::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelUnitDefinition::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(u)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('u.id, u.name, u.sbmlId, u.sboTerm, u.notes, u.annotation, u.symbol, (u.compartmentId) as compartmentId' /*(u.localParameterId) as localParameterId,(u.parameterId) as parameterId'*/);
        $query = QueryRepositoryHelper::addFilterPaginationSortDql($query, $filter, $sort, $limit);

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
			throw new \Exception('Parent of unitDefinition must be ' . $className);
		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelUnitDefinition::class, 'u')
			->where('u.modelId = :modelId')
			->setParameter('modelId', $this->object->getId());
		return $query;
	}
}
