<?php

namespace App\Repositories;

use App\Entity\Atomic;
use App\Entity\AtomicState;
use App\Entity\Compartment;
use App\Entity\Complex;
use App\Entity\Model;
use App\Entity\ModelReaction;
use App\Entity\ModelFunction;
use App\Entity\ModelUnitToDefinition;
use App\Entity\ModelReactionItem;
use App\Entity\EntityStatus;
use App\Entity\IdentifiedObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;


class LocalParameterRepository implements IDependentEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ReactionRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelReaction::class);
	}

	protected static function getParentClassName(): string
	{
		return Model::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelReaction::class, $id);

	}

	public function getParent()
	{
		return $this->object;
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(r)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('r.id, (r.modelId) as modelId,(r.compartmentId) as compartmentId, r.name, r.isReversible, r.isFast, r.rate');

		return $query->getQuery()->getArrayResult();
	}


	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of reaction must be ' . $className);

		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelReaction::class, 'r')
			->where('r.modelId = :modelId')
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
