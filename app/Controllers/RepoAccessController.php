<?php


namespace App\Controllers;


use App\Entity\Authorization\User;
use App\Entity\Authorization\UserGroup;
use App\Entity\Authorization\UserGroupToUser;
use App\Entity\Experiment;
use App\Entity\Model;
use App\Exceptions\InvalidAuthenticationException;
use App\Exceptions\InvalidRoleException;
use App\Repositories\Authorization\UserRepository;
use App\Exceptions\InvalidArgumentException;
use Doctrine\Common\Collections\Criteria;
use Slim\Http\Request;
use League\OAuth2\Server\ResourceServer;


trait RepoAccessController
{


    /**
     * @param string $path
     * @return array with parent name and id
     */
    protected static function getRootParent(string $path)
    {
        $split = explode('/', $path);
        return ['type' => $split[1], 'id' => $split[2]];
    }


    public function hasAccessToObject(array $userGroups): ?int
    {
        $parentClass = null;
        $parent = self::getRootParent($_SERVER['REDIRECT_URL']);
        if($parent['id']) {
            switch ($parent['type']) {
                case 'models':
                    $parentClass = Model::class;
                    break;
                case 'experiments':
                    $parentClass = Experiment::class;
                    break;
                case 'userGroups':
                    $acc_obj = $this->orm->getRepository(UserGroup::class)->find($parent['id']);
                    if (array_key_exists($userGroups, $parent['id']) || $acc_obj->getIsPublic())
                    {
                        return $parent['id'];
                    } else {
                        throw new InvalidAuthenticationException("You cannot access this resource.",
                            "Not a member of the group.");
                    }
                    break;
                case 'users':
                    $user = $this->orm->getRepository(User::class)->find($parent['id']);
                    $groups = $userGroups['group_wise'];
                    $related = $user->getGroups()->map(function (UserGroupToUser $groupLink) use ($groups) {
                        $group = $groupLink->getUserGroupId();
                        return $group->getId() != UserGroup::PUBLIC_SPACE ? !is_null($groups[$group->getId()]) : false;
                    })->toArray();
                    if (array_reduce($related, function($carry, $rel) {return $carry || $rel; }) || $user->getIsPublic()){
                        return true;
                    } else {
                        throw new InvalidAuthenticationException("You cannot access this resource.",
                            "Not public or not a member of the same group.");
                    }
                default:
                    return true;
            }
            $acc_obj = $this->orm->getRepository($parentClass)->find($parent['id']);
            if(property_exists($parentClass, 'groupId') && array_key_exists($acc_obj->getGroupId(), $userGroups))
            {
                return $acc_obj->getGroupId();
            } else {
                throw new InvalidAuthenticationException("You cannot access this resource.",
                    "Not a member of the group.");
            }
        }
        return true;
    }

    public function getAccessFilter(array $userGroups): ?array
    {
        $quasi_filter = [];
        $parentClass = null;
        $parent = self::getRootParent($_SERVER['REDIRECT_URL']);
        switch ($parent['type']) {
            case 'models':
            case 'experiments':
                //TODO? SO HOW DO WE USE GROUPS
                $dql = static::getAlias() . ".groupId";
                $quasi_filter = array_map(function () use ($dql) { return $dql; }, $userGroups);
                break;
            case 'userGroups':
                $dql = static::getAlias() . ".id";
                $acc_obj = $this->orm->getRepository(UserGroup::class)
                    ->matching(Criteria::create()->where(Criteria::expr()->eq('isPublic', true)))
                    ->map(function (UserGroup $group) { return $group->getId();})->toArray();
                foreach (array_flip($acc_obj) as $id => $trash) {
                    $userGroups[$id] = $dql;
                }
                $quasi_filter = array_map(function () use ($dql) { return $dql; }, $userGroups);
                unset($quasi_filter[UserGroup::PUBLIC_SPACE]);
                break;
            case 'users':
                $dql = static::getAlias() . ".id";
                foreach ($this->getVisibleUsersId($userGroups) as $user){
                    $quasi_filter[$user] = $dql;
                }
                break;
            default:
                return null;
        }
        return $quasi_filter;
    }

    public function canManipulate(int $role, int $id, array $can_add): bool
    {
        $parentClass = null;
        $parent = self::getRootParent($_SERVER['REDIRECT_URL']);
        if ($parent['id']) {
            switch ($parent['type']) {
                case 'models':
                case 'experiments':
                    if (!in_array($role, $can_add))
                        return false;
                    break;
                case 'userGroups':
                    if ($role != User::OWNER_ROLE) {
                        return false;
                    }
                    break;
                case 'users':
                    if ($id != $parent['id']){
                        return false;
                    }
                    break;
                default:
                    return false;
            }
        }
    }

    public function canAdd(int $role, int $id) {
        $parentClass = null;
        $parent = self::getRootParent($_SERVER['REDIRECT_URL']);
        if ($parent['id']) {
            switch ($parent['type']) {
                case 'models':
                case 'experiments':
                    if (!in_array($role, User::CAN_ADD)){
                        return false;
                    }
                    break;
                default:
                    return true;
            }
        }
    }

    public function getVisibleUsersId(array $fromGroups){
        $publicUsers = $this->orm->getRepository(User::class)
            ->matching(Criteria::create()->where(Criteria::expr()->eq('isPublic', true)))
            ->map(function (User $user) { return $user->getId(); })->toArray();
        $groupRepo = $this->orm->getRepository(UserGroup::class);
        foreach ($fromGroups as $id => $role){
            if($id != UserGroup::PUBLIC_SPACE){
                $group = $groupRepo->find($id)->getUsers()->map(function (UserGroupToUser $groupLink) {
                    $user = $groupLink->getUserId();
                    return $user->getId();
                })->toArray();
                $publicUsers = array_merge($publicUsers, $group);
            }
        }
        return array_unique($publicUsers);
    }


}