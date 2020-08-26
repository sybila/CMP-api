<?php


namespace App\Controllers;


use App\Exceptions\InvalidAuthenticationException;
use App\Exceptions\InvalidRoleException;
use App\Repositories\Authorization\UserRepository;
use App\Exceptions\InvalidArgumentException;
use Slim\Http\Request;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;


trait RepoAccessController
{

    /**
     * @param Request $request that should be validated
     * @param ResourceServer $server this is what validates
     * @param UserRepository $user this is who needs validation
     * @return array additional collection filter,
     * key (is group id of users groups) => value (is prepared for dql filter)
     * @throws InvalidArgumentException if user with non-existing role
     * @throws InvalidAuthenticationException if auth fails
     */
    protected static function validateList(Request $request, ResourceServer $server, UserRepository $user) : array
    {
        $user_permissions = self::getAccess($request, $server, $user);
        switch ($user_permissions['user_type']){
            case $user::ADMIN:
                return [];
            case $user::POWER:
            case $user::REGISTERED:
            case $user::GUEST:
                $quasi_filter = [];
                $roles = [];
                $target_obj = static::getAlias() . ".groupId";
                foreach ($user_permissions['groups'] as $group) {
                    $roles[$group['groupId']] = $group['roleId'];
                    $quasi_filter[$group['groupId']] = $target_obj;
                }
                $rootParent = self::getRootParent($request->getUri()->getPath());
                $user->hasAccessToObject($rootParent['type'], $rootParent['id'], $roles);
                return $quasi_filter;
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @param Request $request that should be validated
     * @param ResourceServer $server this is what validates
     * @param UserRepository $user this is who needs validation
     * @return bool true - the action was validated, false - not
     * @throws InvalidArgumentException if user with non-existing role
     * @throws InvalidAuthenticationException if auth fails
     */
    protected static function validateDetail(Request $request, ResourceServer $server, UserRepository $user) : bool
    {
        $user_permissions = self::getAccess($request, $server, $user);
        switch ($user_permissions['user_type']){
            case $user::ADMIN:
                return true;
            case $user::POWER:
            case $user::REGISTERED:
            case $user::GUEST:
                $roles = [];
                foreach ($user_permissions['groups'] as $group) {
                    $roles[$group['groupId']] = $group['roleId'];
                }
                $rootParent = self::getRootParent($request->getUri()->getPath());
                $user->hasAccessToObject($rootParent['type'], $rootParent['id'], $roles);
                return true;
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @param Request $request that should be validated
     * @param ResourceServer $server this is what validates
     * @param UserRepository $user this is who needs validation
     * @return bool true - the action was validated, false - not
     * @throws InvalidArgumentException if user with non-existing role
     * @throws InvalidAuthenticationException if auth fails
     * @throws InvalidRoleException if role permissions are not enough
     */
    protected static function validateAdd(Request $request, ResourceServer $server, UserRepository $user) : bool
    {
        $user_permissions = self::getAccess($request, $server, $user);
        switch ($user_permissions['user_type']){
            case $user::ADMIN:
                return true;
            case $user::POWER:
            case $user::REGISTERED:
                $roles = [];
                foreach ($user_permissions['groups'] as $group) {
                    $roles[$group['groupId']] = $group['roleId'];
                }
                $rootParent = self::getRootParent($request->getUri()->getPath());
                $user_group = $user->hasAccessToObject($rootParent['type'], $rootParent['id'], $roles);
                if (!in_array($roles[$user_group], $user::CAN_ADD)  ||
                    in_array($rootParent['type'],['users', 'userGroups'])){
                    throw new InvalidRoleException('add', 'POST',
                        $request->getUri()->getPath());
                }
                return true;
            case $user::GUEST:
                throw new InvalidRoleException('add', 'POST', $request->getUri()->getPath());
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @param Request $request that should be validated
     * @param ResourceServer $server this is what validates
     * @param UserRepository $user this is who needs validation
     * @return bool true - the action was validated, false - not
     * @throws InvalidArgumentException if user with non-existing role
     * @throws InvalidAuthenticationException if auth fails
     * @throws InvalidRoleException if role permissions are not enough
     */
    protected static function validateEdit(Request $request, ResourceServer $server, UserRepository $user) : bool
    {
        $user_permissions = self::getAccess($request, $server, $user);
        switch ($user_permissions['user_type']){
            case $user::ADMIN:
                return true;
            case $user::POWER:
            case $user::REGISTERED:
                $roles = [];
                foreach ($user_permissions['groups'] as $group) {
                    $roles[$group['groupId']] = $group['roleId'];
                }
                $rootParent = self::getRootParent($request->getUri()->getPath());
                $user_group = $user->hasAccessToObject($rootParent['type'], $rootParent['id'], $roles);
                if (!in_array($roles[$user_group], $user::CAN_EDIT)){
                    throw new InvalidRoleException('edit', 'PUT',
                        $request->getUri()->getPath());
                }
                return true;
            case $user::GUEST:
                throw new InvalidRoleException('edit', 'PUT', $request->getUri()->getPath());
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @param Request $request that should be validated
     * @param ResourceServer $server this is what validates
     * @param UserRepository $user this is who needs validation
     * @return bool true - the action was validated, false - not
     * @throws InvalidArgumentException if user with non-existing role
     * @throws InvalidAuthenticationException if auth fails
     * @throws InvalidRoleException if role permissions are not enough
     */
    protected static function validateDelete(Request $request, ResourceServer $server, UserRepository $user) : bool
    {
        $user_permissions = self::getAccess($request, $server, $user);
        switch ($user_permissions['user_type']){
            case $user::ADMIN:
                return true;
            case $user::POWER:
            case $user::REGISTERED:
                $roles = [];
                foreach ($user_permissions['groups'] as $group) {
                    $roles[$group['groupId']] = $group['roleId'];
                }
                $rootParent = self::getRootParent($request->getUri()->getPath());
                $user_group = $user->hasAccessToObject($rootParent['type'], $rootParent['id'], $roles);
                if (!in_array($roles[$user_group], $user::CAN_DELETE) ||
                    in_array($rootParent['type'],['users', 'userGroups'])){
                    throw new InvalidRoleException('delete', 'DELETE',
                        $request->getUri()->getPath());
                }
                return true;
            case $user::GUEST:
                throw new InvalidRoleException('delete', 'DELETE', $request->getUri()->getPath());
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    protected static function validationNotByGroup(string $api_action, string $resource_type, int $u_id){

    }

    /**
     * @param Request $request needed for auth.
     * @param ResourceServer $server handles the authentication.
     * @param UserRepository $user is who needs the access.
     * @return array key 'groups' contains array of arrays of keys [groupId, groupRole], key user_type contains
     * the type of the user in int (1-4) ~> admin, power, registered, guest.
     * @throws InvalidAuthenticationException
     */
    protected static function getAccess(Request $request, ResourceServer $server, UserRepository $user): array
    {
        try {
            $request = $server->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $e) {
            throw new InvalidAuthenticationException($e->getMessage());
        }
        $u_id = $request->getAttribute('oauth_user_id');

        return ['groups' => $user->getGroups($u_id), 'user_type' => $user->getById($u_id)->getType(),
            'user_id' => $u_id];
    }


    /**
     * @param string $path
     * @return array with parent name and id
     */
    protected static function getRootParent(string $path)
    {
        $split = explode('/', $path);
        return ['type' => $split[1], 'id' => $split[2]];
    }

}