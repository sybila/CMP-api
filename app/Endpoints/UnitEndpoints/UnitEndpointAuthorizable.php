<?php

use App\Entity\Authorization\User;

trait UnitEndpointAuthorizable
{

//    public function hasAccessToObject(array $userGroups): ?int
//    {
//        // TODO: Implement hasAccessToObject() method.
//    }
//
//    public function getAccessFilter(array $userGroups): ?array
//    {
//        // TODO: Implement getAccessFilter() method.
//    }

    public function canAdd(int $role, int $id): bool
    {
        $parent = self::getRootParent();
        if ($parent['id']) {
            if (!in_array($role, User::CAN_ADD)){
                return false;
            }
        }
        return true;
    }

//    public function canEdit(int $role, int $id): bool
//    {
//        // TODO: Implement canEdit() method.
//    }
//
//    public function canDelete(int $role, int $id): bool
//    {
//        // TODO: Implement canDelete() method.
//    }
}