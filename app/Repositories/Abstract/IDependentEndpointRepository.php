<?php

namespace App\Entity\Repositories;

use App\Entity\IdentifiedObject;
use App\Exceptions\WrongParentException;

interface IDependentEndpointRepository extends IEndpointRepository
{
    public function getParent(): IdentifiedObject;

    /**
     * @param IdentifiedObject $object
     * @throws WrongParentException
     */
	public function setParent(IdentifiedObject $object): void;
	public function add($object): void;
	public function remove($object): void;

}
