<?php

use App\Entity\Authorization\User;
use App\Entity\Experiment;
use App\Exceptions\InvalidAuthenticationException;
use App\Exceptions\WrongParentException;

trait ExperimentEndpointAccessible
{
    /**
     * Gets the eldest parent (root of the SLIM route) and checks whether this object
     * is accessible by the current user. If no id is provided in the request for the eldest parent,
     * it returns null, as it means that GET collection was requested. This api action is not
     * protected, only the displayed data is being filtered by their visibility or the relation of the current user
     * to the resource via the group.
     * @param array $userGroups
     * @return int|null
     * @throws InvalidAuthenticationException
     * @throws WrongParentException
     */
    public function hasAccessToObject(array $userGroups): ?int
    {
        $rootRouteParent = self::getRootParent();
        //if there is no id, it means GET LIST was requested.
        if (is_null($rootRouteParent['id'])) {
            return null;
        }
        if ($rootRouteParent['type'] == ('experiments'||'experimentvalues') ) {
            /** @var Experiment $routeParentObject */
            $routeParentObject = $this->getObjectViaORM(Experiment::class, $rootRouteParent['id']);
            if(array_key_exists($routeParentObject->getGroupId(), $userGroups)) {
                return $routeParentObject->getGroupId();
            } else {
                throw new InvalidAuthenticationException("You cannot access this resource.",
                    "Not a member of the group.");
            }
        }
        else {
            throw new WrongParentException($rootRouteParent['type'], $rootRouteParent['id'],
                $this->getObjectName(), null);
        }
    }

    public function getAccessFilter(array $userGroups): ?array
    {
        $dql = static::getAlias() . ".groupId";
        return array_map(function () use ($dql) { return $dql; }, $userGroups);
    }

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

    public function canEdit(int $role, int $id): bool
    {
        $parent = self::getRootParent();
        if ($parent['id']) {
            if (!in_array($role, User::CAN_EDIT)){
                return false;
            }
        }
        return true;
    }

    public function canDelete(int $role, int $id): bool
    {
        $parent = self::getRootParent();
        if ($parent['id']) {
            if (!in_array($role, User::CAN_DELETE)){
                return false;
            }
        }
        return true;
    }
}