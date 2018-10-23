<?php

namespace App\Entity\Repositories;

use App\Entity\ModelSpecie;
use App\Entity\ModelCompartment;
use App\Entity\IdentifiedObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


class SpecieRepository implements IDependentEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\SpecieRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelSpecie::class);
	}

	protected static function getParentClassName(): string
	{
		return ModelCompartment::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelSpecie::class, $id);

	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(s)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('s.id, s.name, s.equationType, s.initialExpression, s.hasOnlySubstanceUnits, s.isConstant, s.boundaryCondition');


		return $query->getQuery()->getArrayResult();
	}

	public function getParent() {
		return $this->object;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of specie must be ' . $className);

		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelSpecie::class, 's')
			->where('s.compartmentId = :compartmentId')
			->setParameter('compartmentId', $this->object->getId());


		return $query;


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
