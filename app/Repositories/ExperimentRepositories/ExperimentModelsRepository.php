<?php

namespace App\Entity\Repositories;

use App\Controllers\ModelController;
use App\Entity\Experiment;
use App\Entity\ExperimentModels;
use App\Entity\IdentifiedObject;
use App\Entity\Model;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentModelsRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ExperimentModelsRepository */
	private $repository;

	/** @var Experiment */
	private $experiment;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ExperimentModels::class);
	}

	protected static function getParentClassName(): string
	{
		return Experiment::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ExperimentModels::class, $id);
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
			->select('c.id', 'IDENTITY(c.ModelId)' , 'c.validated', 'c.userId');
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
			throw new \Exception('Parent of experiment-model must be ' . $className);
		$this->experiment = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ExperimentModels::class, 'c')
			->where('c.ExperimentId = :ExperimentId')
			->setParameter('ExperimentId', $this->experiment->getId());
		return $query;
	}
}
