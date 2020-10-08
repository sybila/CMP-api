<?php

use App\Exceptions\InvalidRoleException;

interface IGroupRoleAuthController
{

    /**
     * Returns the array for filter.
     * THROWS InvalidRoleException if user with non-existing role
     * @return array|null
     * @throws InvalidRoleException
     */
    public function validateList(): ?array;

    /**
     * Returns TRUE if the user can request
     * the GET (../entities/$ID} method.
     * THROWS InvalidRoleException otherwise.
     * @return bool
     * @throws InvalidRoleException
     */
    public function validateDetail(): bool;


}