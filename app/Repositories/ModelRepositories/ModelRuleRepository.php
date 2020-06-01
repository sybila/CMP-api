<?php

namespace App\Entity\Repositories;

use App\Entity\ModelRule;
use App\Entity\Model;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelRuleRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ModelRuleRepository */
	private $repository;

	/** @var Model */
	private $model; //object

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelRule::class);
	}

	protected static function getParentClassName(): string
	{
		return Model::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelRule::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(r)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('r.id, (r.modelId) AS modelId');
        $query = QueryRepositoryHelper::addFilterPaginationSortDql($query, $filter, $sort, $limit);

		return $query->getQuery()->getArrayResult();
	}


	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of rule must be ' . $className);

		$this->model = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelRule::class, 'r')
			->where('r.modelId = :thisModelId')
			->setParameter('thisModelId', $this->model->getId());
		return $query;
	}

	public function getParent(): IdentifiedObject
	{
		return $this->model;
	}
}
