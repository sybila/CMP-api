<?php

use App\Exceptions\InvalidRoleException;

interface IGroupRoleAuthWritableController extends IGroupRoleAuthController
{
    /**
     * Returns TRUE if the user can request
     * the POST (../entities} method.
     * THROWS InvalidRoleException otherwise.
     * @return bool
     * @throws InvalidRoleException
     */
    public function validateAdd(): bool;

    /**
     * Returns TRUE if the user can request
     * the DELETE (../entities/$ID} method.
     * THROWS InvalidRoleException otherwise.
     * @return bool
     * @throws InvalidRoleException
     */
    public function validateDelete(): bool;

    /**
     * Returns TRUE if the user can request
     * the PUT (../entities/$ID} method.
     * THROWS InvalidRoleException otherwise.
     * @return bool
     * @throws InvalidRoleException
     */
    public function validateEdit(): bool;
}