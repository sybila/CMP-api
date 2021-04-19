<?php

use App\Exceptions\InvalidRoleException;

/**
 * Interface IPlatformRoleAuthController
 * @author Radoslav Doktor 433286@mail.muni.cz
 */
interface IPlatformRoleAuthController
{
    /**
     * Returns the array for filter.
     * THROWS InvalidRoleException if user with non-existing role
     * @return bool
     * @throws InvalidRoleException
     */
    public function validateList(): bool;

    /**
     * Returns TRUE if the user can request
     * the GET (../entities/$ID} method.
     * THROWS InvalidRoleException otherwise.
     * @return bool
     * @throws InvalidRoleException
     */
    public function validateDetail(): bool;
}
