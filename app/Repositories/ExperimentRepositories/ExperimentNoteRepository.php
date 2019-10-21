<?php

namespace App\Entity\Repositories;

use App\Entity\Experiment;
use App\Entity\ExperimentNote;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentNoteRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\NoteRepository */
	private $repository;

	/** @var Experiment */
	private $experiment;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ExperimentNote::class);
	}

	protected static function getParentClassName(): string
	{
		return Experiment::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ExperimentNote::class, $id);
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
			->select('c.id, c.time, c.note, c.imgLink');
		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->experiment;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of note must be ' . $className);
		$this->experiment = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ExperimentNote::class, 'c')
			->where('c.experimentId = :experimentId')
			->setParameter('experimentId', $this->experiment->getId());
		return $query;
	}

    public function add($object): void
    {
    }

    public function remove($object): void
    {
    }
}
