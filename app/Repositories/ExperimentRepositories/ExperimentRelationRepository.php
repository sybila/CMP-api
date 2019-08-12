<?php

namespace App\Entity\Repositories;

use App\Controllers\ExperimentController;
use App\Entity\Experiment;
use App\Entity\ExperimentRelation;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentRelationRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\RelationRepository */
	private $repository;

	/** @var Experiment */
	private $experiment;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ExperimentRelation::class);
	}

	protected static function getParentClassName(): string
	{
		return Experiment::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ExperimentRelation::class, $id);
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
			->select('IDENTITY(er.secondExperimentId)', 'e.id', 'e.name', 'e.description', 'e.protocol', 'e.started', 'e.inserted', 'e.status')
            ->from('App\Entity\Experiment', 'e')
            ->innerJoin('App\Entity\ExperimentRelation', 'er', \Doctrine\ORM\Query\Expr\Join::WITH, 'er.secondExperimentId = e.id');
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
			throw new \Exception('Parent of relation must be ' . $className);
		$this->experiment = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ExperimentRelation::class, 'c')
			->where('c.secondExperimentId = :secondExperimentId')
			->setParameter('secondExperimentId', $this->experiment->getId());
		return $query;
	}
}
