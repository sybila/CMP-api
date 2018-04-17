<?php

namespace App\Entity\Repositories;

interface PageableRepository
{
	public function getNumResults(array $filter);
}
