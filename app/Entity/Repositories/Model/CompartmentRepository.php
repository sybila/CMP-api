<?php

namespace App\Entity\Repositories;

use App\Entity\Atomic;
use App\Entity\AtomicState;
use App\Entity\Compartment;
use App\Entity\Complex;
use App\Entity\Model;
use App\Entity\ModelCompartment;
use App\Entity\EntityStatus;
use App\Entity\IdentifiedObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


class CompartmentRepository implements IDependentEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\CompartmentRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelCompartment::class);
	}

	protected static function getParentClassName(): string
	{
		return Model::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelCompartment::class, $id);

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
			->select('c.id, c.name, c.spatialDimensions, c.size, c.isConstant');

		return $query->getQuery()->getArrayResult();
	}

	public function getParent():IdentifiedObject {
		return $this->object;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of compartment must be ' . $className);
		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelCompartment::class, 'c')
			->where('c.modelId = :modelId')
			->setParameter('modelId', $this->object->getId());
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
