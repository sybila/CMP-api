<?php

namespace App\Entity\Repositories;

interface IRepository
{
	public function get(int $id);
	public function getNumResults(array $filter): int;
	public function getList(array $filter, array $sort, array $limit): array;
}
