<?php

interface IAuthWritableRepositoryController extends IAuthRepositoryController
{
    public function canAdd(int $role, int $id): bool;
    public function canEdit(int $role, int $id): bool;
    public function canDelete(int $role, int $id): bool;
}