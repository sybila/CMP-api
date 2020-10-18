<?php

namespace App\Entity\Repositories;

use App\Entity\IdentifiedObject;
use App\Entity\PhysicalQuantity;
use App\Entity\Unit;
use App\Entity\UnitAlias;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class UnitAliasRepository implements IDependentSBaseRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\UnitAliasRepository */
	private $repository;

	/** @var Unit */
	private $unit;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(UnitAlias::class);
	}

	protected static function getParentClassName(): string
	{
		return Unit::class;
	}

	public function get(int $id)
	{
		return $this->em->find(UnitAlias::class, $id);
	}

    protected static function alias(): string
    {
        return 'a';
    }

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(a)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('a.id, a.alternative_name');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->unit;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of note must be ' . $className);
		$this->unit = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(UnitAlias::class, 'a')
			->where('a.unitId = :unitId')
			->setParameter('unitId', $this->unit->getId());
        $query = $this->addFilterDql($query, $filter);
		return $query;
	}

    public function add($object): void
    {
    }

    public function remove($object): void
    {
    }
}
