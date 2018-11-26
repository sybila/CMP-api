<?php

namespace App\Entity\Repositories;

use App\Entity\IdentifiedObject;

interface IDependentEndpointRepository extends IEndpointRepository
{
	public function setParent(IdentifiedObject $object): void;
	public function add($object): void;
	public function remove($object): void;

}

interface IAssociatedRepository extends IDependentEndpointRepository
{
	public function add($object): void;
	public function remove($object): void;
}
