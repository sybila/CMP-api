<?php

namespace App\Entity\Repositories;

use App\Entity\Atomic;
use App\Entity\AtomicState;
use App\Entity\Compartment;
use App\Entity\Complex;
use App\Entity\Entity;
use App\Entity\Model;
use App\Entity\EntityStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


class ModelRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ModelRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Model::class);
	}

	public function get(int $id)
	{
		return $this->em->find(Model::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(m)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('m.id, m.name, m.unitId, m.userId, m.status, m.solver');


		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Model::class, 'm');


		return $query;
	}


}
