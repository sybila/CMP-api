<?php

namespace App\Entity\Repositories;

use App\Entity\IdentifiedObject;

interface IDependentRepository extends IRepository
{
	public function setParent(IdentifiedObject $object): void;
	public function add($object): void;
	public function remove($object): void;
}
