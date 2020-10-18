<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\UserGroup;
use App\Entity\Authorization\UserType;
use App\Entity\EntityRepository;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IEndpointRepository;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class UserGroupRepository implements IEndpointRepository
{

    use QueryRepositoryHelper;

	/** @var EntityManager */
	private $em;

	/** @var EntityRepository */
	private $userGroupRepository;

	/** @var UserGroup */
	private $userGroup;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->userGroupRepository = $em->getRepository(UserGroup::class);
	}

    protected static function alias(): string
    {
        return 'g';
    }

	public function getById(int $id)
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
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
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
		return $this->addFilterDql($query, $filter);
	}

}
