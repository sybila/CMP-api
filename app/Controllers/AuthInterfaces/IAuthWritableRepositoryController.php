<?php

interface IAuthWritableRepositoryController extends IAuthRepositoryController
{
    /**
     * Returns true if this user can POST on these endpoints.
     * False otherwise
     * @param int $role
     * @param int $id
     * @return bool
     */
    public function canAdd(int $role, int $id): bool;

    /**
     * Returns true if this user can PUT on these endpoints.
     * False otherwise
     * @param int $role
     * @param int $id
     * @return bool
     */
    public function canEdit(int $role, int $id): bool;

    /**
     * Returns true if this user can DELETE these endpoints.
     * False otherwise
     * @param int $role
     * @param int $id
     * @return bool
     */
    public function canDelete(int $role, int $id): bool;
}