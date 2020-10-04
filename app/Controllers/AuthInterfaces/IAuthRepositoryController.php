<?php

interface IAuthRepositoryController
{
    /**
     * Check whether the user has access to the endpoint object. Check if user groups
     * matches with the groups defined on the object on the root of the slim ROUTE.
     * @param array $userGroups
     * @return int|null
     */
    public function hasAccessToObject(array $userGroups): ?int;

    /**
     * Returns array with prepared DQL filter depending on access method applied on the resources.
     * Applied when GET collection is triggered.
     * @param array $userGroups
     * @return array|null
     */
    public function getAccessFilter(array $userGroups): ?array;
}
