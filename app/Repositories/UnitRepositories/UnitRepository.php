<?php

namespace App\Entity\Repositories;

use App\Entity\IdentifiedObject;
use App\Entity\PhysicalQuantity;
use App\Entity\Unit;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class UnitRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\UnitRepository */
	private $repository;

	/** @var PhysicalQuantity */
	private $physicalQuantity;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Unit::class);
	}

	protected static function getParentClassName(): string
	{
		return PhysicalQuantity::class;
	}

	public function get(int $id)
	{
		return $this->em->find(Unit::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(u)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('u.id, u.preferred_name, u.coefficient, u.sbmlId');
        $query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);
		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->physicalQuantity;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of note must be ' . $className);
		$this->physicalQuantity = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Unit::class, 'u')
			->where('u.quantityId = :quantityId')
			->setParameter('quantityId', $this->physicalQuantity->getId());
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
