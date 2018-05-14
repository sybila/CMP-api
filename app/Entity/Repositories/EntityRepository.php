<?php

namespace App\Entity\Repositories;

use App\Entity\Atomic;
use App\Entity\AtomicState;
use App\Entity\Compartment;
use App\Entity\Complex;
use App\Entity\Entity;
use App\Entity\EntityStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

interface EntityRepository extends IEndpointRepository
{
	public function getByCode(string $code): ?Entity;

	/**
	 * @param Compartment $entity
	 * @return Entity[]|ArrayCollection
	 */
	public function findCompartmentComponents(Compartment $entity): ArrayCollection;

	/**
	 * @param Atomic $entity
	 * @return AtomicState[]|ArrayCollection
	 */
	public function findAtomicStates(Atomic $entity): ArrayCollection;
}

class EntityRepositoryImpl implements EntityRepository
{
	/** @var EntityManager */
	private $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Entity::class);
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Entity::class, 'e')
			->where('e NOT INSTANCE OF \App\Entity\AtomicState');

		if (isset($filter['name']))
		{
			$i = 0;
			foreach (explode(' ', $filter['name']) as $namePart)
			{
				$query->setParameter($i, '%' . $namePart . '%');
				$query->andWhere('e.name LIKE ?' . $i++);
			}
		}

		if (isset($filter['annotation']))
		{
			$query->innerJoin('e.annotations', 'ea')
				->andWhere('ea.termType = :type')
				->andWhere('ea.termId = :id')
				->setParameters($filter['annotation']);
		}

		return $query;
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('e.id, e.name, e.description, e.code, e.status, TYPE(e) as type');

		foreach ($sort as $by => $how)
		{
			if ($by != 'type')
				$by = 'e.' . $by;

			$query->addOrderBy($by, $how ?: null);
		}

		if ($limit['limit'] > 0)
		{
			$query->setMaxResults($limit['limit'])
				->setFirstResult($limit['offset']);
		}

		return array_map(function(array $input)
		{
			$input['type'] = Entity::$dataToType[$input['type']];
			$input['status'] = (string)EntityStatus::fromInt($input['status'] ?: 1);
			return $input;
		}, $query->getQuery()->getArrayResult());
	}

	public function getNumResults(array $filter): int
	{
		return (int)$this->buildListQuery($filter)
			->select('COUNT(e)')
			->getQuery()
			->getScalarResult()[0][1];
	}

	public function findCompartmentComponents(Compartment $entity): ArrayCollection
	{
		return new ArrayCollection($this->em
			->createQuery('SELECT e FROM \\App\\Entity\\Entity e INNER JOIN e.compartments em WHERE em.id = :id AND e NOT INSTANCE OF \\App\\Entity\\Compartment')
			->setParameters(['id' => $entity->getId()])
			->getResult());
	}

	public function findAtomicStates(Atomic $entity): ArrayCollection
	{
		return new ArrayCollection($this->em
			->createQuery('SELECT s FROM \\App\\Entity\\AtomicState s WHERE s.parent = :id')
			->setParameters(['id' => $entity->getId()])
			->getResult());
	}

	public function get(int $id): ?Entity
	{
		return $this->em->find(Entity::class, $id);
	}

	public function getByCode(string $code): ?Entity
	{
		return $this->repository->findOneBy(['code' => $code]);
	}
}

