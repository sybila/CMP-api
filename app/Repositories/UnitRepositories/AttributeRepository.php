<?php

namespace App\Entity\Repositories;

use App\Entity\Attribute;
use App\Entity\IdentifiedObject;
use App\Entity\PhysicalQuantity;
use App\Entity\Unit;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class AttributeRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\AttributRepository */
	private $repository;

	/** @var PhysicalQuantity */
	private $physicalQuantity;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Attribute::class);
	}

	protected static function getParentClassName(): string
	{
		return PhysicalQuantity::class;
	}

	public function get(int $id)
	{
		return $this->em->find(Attribute::class, $id);
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
			->select('a.id, a.name, a.note');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
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
			->from(Attribute::class, 'a')
			->where('a.quantityId = :quantityId')
			->setParameter('quantityId', $this->physicalQuantity->getId());
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
