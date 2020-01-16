<?php

namespace App\Entity\Repositories;

use App\Entity\Bioquantity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class BioquantityRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\BioquantityRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Bioquantity::class);
	}

	public function get(int $id)
	{
		return $this->em->find(Bioquantity::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(b)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('b.id, b.name, b.description, b.IsAutomatic, b.IsValid');
		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Bioquantity::class, 'b');
		return $query;
	}

}
