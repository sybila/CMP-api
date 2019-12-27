<?php

namespace App\Entity\Repositories;

use App\Entity\Experiment;
use App\Entity\ExperimentEvent;
use App\Entity\ExperimentEventArg;
use App\Entity\ExperimentValues;
use App\Entity\ExperimentVariable;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentEventArgRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	private $em;

	/** @var \Doctrine\ORM\EventArgRepository */
	private $repository;

	/** @var ExperimentEvent */
	private $event;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ExperimentEventArg::class);
	}

	protected static function getParentClassName(): string
	{
		return ExperimentEvent::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ExperimentEventArg::class, $id);
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
			->select('c.id, c.value');

        return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->event;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of variable must be ' . $className);
		$this->event = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ExperimentEventArg::class, 'c')
			->where('c.eventId = :eventId')
			->setParameter('eventId', $this->event->getId());
		return $query;
	}

    /**
     * @param ExperimentEvent $object
     */
    public function add($object): void
    {
    }

    public function remove($object): void
    {
    }
}
