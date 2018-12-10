<?php

namespace App\Entity\Repositories;

use App\Entity\IdentifiedObject;

interface IDependentSBaseRepository extends IEndpointRepository
{
	public function setParent(IdentifiedObject $object): void;

	public function getParent(): IdentifiedObject;
}