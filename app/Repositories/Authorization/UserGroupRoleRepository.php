<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\UserGroupRole;
use App\Entity\Repositories\IEndpointRepository;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class UserGroupRoleRepository implements IEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager */
	private $em;

	/** @var EntityRepository */
	private $userGroupRoleRepository;

	/** @var UserGroupRole */
	private $UserGroupRole;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->userGroupRoleRepository = $em->getRepository(UserGroupRole::class);
	}

    protected static function alias(): string
    {
        return 'gr';
    }

	public function getById(int $id)
	{
		return $this->userGroupRoleRepository->find($id);
	}


	public function get(int $id)
	{
		return $this->em->find(UserGroupRole::class, $id);
	}


	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('gr.id, gr.tier, gr.name');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
		return $query->getQuery()->getArrayResult();
	}


	public function getNumResults(array $filter): int
	{
		return ((int) $this->buildListQuery($filter)
				->select('COUNT(gr)')
				->getQuery()
				->getScalarResult());
	}


	private function buildListQuery(array $filter): QueryBuilder
	{
        return $this->em->createQueryBuilder()
            ->from(UserGroupRole::class, 'gr');
	}

}
