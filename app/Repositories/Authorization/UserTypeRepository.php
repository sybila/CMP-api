<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\UserType;
use App\Entity\Repositories\IEndpointRepository;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class UserTypeRepository implements IEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager */
	private $em;

	/** @var ObjectRepository */
	private $userTypeRepository;

	/** @var UserType */
	private $UserType;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->userTypeRepository = $em->getRepository(UserType::class);
	}

    protected static function alias(): string
    {
        return 'ut';
    }

	public function getById(int $id): ?UserType
	{
		return $this->userTypeRepository->find($id);
	}


	public function get(int $id)
	{
		return $this->em->find(UserType::class, $id);
	}


	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('ut.id, ut.tier, ut.name');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
		return $query->getQuery()->getArrayResult();
	}


	public function getNumResults(array $filter): int
	{
		return ((int) $this->buildListQuery($filter)
				->select('COUNT(ut)')
				->getQuery()
				->getScalarResult());
	}


	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(UserType::class, 'ut');
		return $query;
	}

}
