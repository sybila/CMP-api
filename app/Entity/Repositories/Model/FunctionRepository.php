<?php

namespace App\Entity\Repositories;

use App\Entity\Atomic;
use App\Entity\AtomicState;
use App\Entity\Compartment;
use App\Entity\Complex;
use App\Entity\Model;
use App\Entity\ModelFunction;
use App\Entity\ModelReaction;
use App\Entity\ModelSpecie;
use App\Entity;
use App\Entity\EntityStatus;
use App\Entity\IdentifiedObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


class FunctionRepository implements IDependentEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ModelFunction */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelFunction::class);
	}

	protected static function getParentClassName(): string
	{
		return ModelReaction::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelFunction::class, $id);

	}

	public function getParent()
	{
		return $this->object;
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(f)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('f.id, f.name, f.formula');

		return $query->getQuery()->getArrayResult();
	}


	public function setParent(IdentifiedObject $object): void
	{
		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelFunction::class, 'f')
				->where('f.reactionId = :reactionId')
		->setParameter('reactionId', $this->object->getId());

		return $query;
	}


	public function add($object): void
	{
		// TODO: Refactor
	}

	public function remove($object): void
	{
		// TODO: Refactor
	}
}
