<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelRepository implements IEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Model::class);
	}

	public function get(int $id)
	{
		return $this->em->find(Model::class, $id);
	}

    protected static function alias(): string
    {
        return 'm';
    }

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(m)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('m.id, m.name, m.userId, m.groupId, m.description, m.status, m.isPublic');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Model::class, 'm')
            ->orWhere('m.isPublic = TRUE');
		$query = $this->addFilterDql($query, $filter);
		return $query;
	}

}
