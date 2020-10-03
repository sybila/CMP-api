<?php


namespace App\Controllers;



trait DefaultControllerAccessible
{

    /**
     * @return array with root parent name and id
     */
    protected static function getRootParent()
    {
        $split = array_diff(explode("/" , $_SERVER['REQUEST_URI']), explode("/", $_SERVER['SCRIPT_NAME']));
        return ['type' => array_shift($split), 'id' => array_shift($split)];
    }


    public function hasAccessToObject(array $userGroups): ?int
    {
        return null;
    }

    public function getAccessFilter(array $userGroups): ?array
    {
        return null;
    }

    public function canEdit(int $role, int $id): bool
    {
        return false;
    }

    public function canDelete(int $role, int $id): bool
    {
        return false;
    }

    public function canAdd(int $role, int $id): bool
    {
        return false;
    }

}