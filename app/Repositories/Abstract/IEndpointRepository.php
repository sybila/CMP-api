<?php

namespace App\Entity\Repositories;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\UnexpectedResultException;

interface IEndpointRepository extends IRepository
{
    /**
     * @param int $id
     * @return mixed
     * @throws ORMException
     */
	public function get(int $id);

    /**
     * @param array $filter
     * @return int
     * @throws UnexpectedResultException
     */
	public function getNumResults(array $filter): int;
	public function getList(array $filter, array $sort, array $limit): array;
}
