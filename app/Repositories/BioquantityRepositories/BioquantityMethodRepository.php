<?php

namespace App\Entity\Repositories;

use App\Entity\Bioquantity;
use App\Entity\BioquantityMethod;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class BioquantityMethodRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\MethodRepository */
	private $repository;

	/** @var Bioquantity */
	private $bioquantity;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(BioquantityMethod::class);
	}

	protected static function getParentClassName(): string
	{
		return Bioquantity::class;
	}

	public function get(int $id)
	{
		return $this->em->find(BioquantityMethod::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(c)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('c.id, c.value, c.formula, c.source');
		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->bioquantity;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of method must be ' . $className);
		$this->bioquantity = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(BioquantityMethod::class, 'c')
			->where('c.bioquantityId = :bioquantityId')
			->setParameter('bioquantityId', $this->bioquantity->getId());
		return $query;
	}
}
