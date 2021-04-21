<?php


namespace App\Controllers;



use App\Entity\Authorization\User;

/**
 * Trait DefaultControllerAccessible
 * @package App\Controllers
 * @author Radoslav Doktor 433286@mail.muni.cz
 */
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
        if ($this->userPermissions['platform_wise'] == User::ADMIN){
            return [];
        }
        return null;
    }

    public function canList(?int $role, ?int $id): bool
    {
        return true;
    }

    public function canDetail(?int $role, ?int $id): bool
    {
        return true;
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