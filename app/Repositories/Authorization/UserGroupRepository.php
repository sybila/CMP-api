<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\UserGroup;
use App\Entity\Authorization\UserType;
use App\Entity\Repositories\IEndpointRepository;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class UserGroupRepository implements IEndpointRepository
{

	/** @var EntityManager */
	private $em;

	/** @var ObjectRepository */
	private $userGroupRepository;

	/** @var UserGroup */
	private $userGroup;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->userGroupRepository = $em->getRepository(UserGroup::class);
	}


	public function getById(int $id): ?UserGroup
	{
		return $this->userGroupRepository->find($id);
	}


	public function get(int $id)
	{
		return $this->em->find(UserGroup::class, $id);
	}


	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('g.id, g.name, g.type, g.description');
		return $query->getQuery()->getArrayResult();

	}


	public function getNumResults(array $filter): int
	{
		return ((int) $this->buildListQuery($filter)
				->select('COUNT(g)')
				->getQuery()
				->getScalarResult());
	}


	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(UserGroup::class, 'g');
		return QueryRepositoryHelper::addFilterDql($query, $filter);
	}

}
