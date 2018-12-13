<?php

namespace App\Entity\Repositories;

use App\Entity\IdentifiedObject;

interface ISBaseRepository extends IRepository
{
	public function get(int $id);
	public function getNumResults(array $filter): int;
	public function getList(array $filter, array $sort, array $limit): array;
}

interface IDependentSBaseRepository extends ISbaseRepository
{
	public function setParent(IdentifiedObject $object): void;

	public function getParent(): IdentifiedObject;
}

