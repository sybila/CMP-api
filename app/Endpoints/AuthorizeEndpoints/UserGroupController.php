<?php

namespace App\Controllers;

use App\Repositories\Authorization\UserRepository;
use App\Entity\{
	Authorization\User,
	Authorization\UserGroup,
	Authorization\UserGroupToUser,
	IdentifiedObject
};
use App\Entity\Repositories\IEndpointRepository;
use App\Repositories\Authorization\UserGroupRepository;
use App\Exceptions\{InvalidAuthenticationException, NonExistingObjectException};
use App\Helpers\ArgumentParser;
use Doctrine\Common\Collections\Criteria;
use IGroupRoleAuthWritableController;
use Slim\Http\{
	Request,
	Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Radoslav Doktor & Jakub Hrabec & Marek Havlik
 * @property-read UserGroupRepository $repository
 * @method IdentifiedObject getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class UserGroupController extends WritableRepositoryController implements IGroupRoleAuthWritableController
{

	protected static function getAllowedSort(): array
	{
		return ['id, name'];
	}


	protected function getData(IdentifiedObject $userGroup): array
	{
		/** @var UserGroup $userGroup */
		return [
			'id' => $userGroup->getId(),
			'name' => $userGroup->getName(),
			'type' => (int) $userGroup->getType(),
			'description' => $userGroup->getDescription(),
			'users' => $userGroup->getUsers()->map(function (UserGroupToUser $userLink) {
					$user = $userLink->getUserId();
					return ['role' => $userLink->getRoleId(), 'id' => $user->getId(), 'name' => $user->getName(), 'surname' => $user->getSurname()];
				})->toArray(),
		];
	}


	protected function setData(IdentifiedObject $userGroup, ArgumentParser $body): void
	{
		/** @var UserGroup $userGroup */
		!$body->hasKey('name') ?: $userGroup->setName($body->getString('name'));
        if ($body->hasKey('type')) {
            $type = $body->getString('type');
            if (array_key_exists($type, UserGroup::SPACES)) {
                $type = UserGroup::SPACES[$type];
            }
            $userGroup->setType($type);
        }
		!$body->hasKey('description') ?: $userGroup->setDescription($body->getString('description'));
	}


	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		$this->verifyMandatoryArguments(['name', 'type', 'description'], $body);
		return new UserGroup;
	}


	protected function checkInsertObject(IdentifiedObject $userGroup): void
	{
		$userGroup->setIsPublic(intval($userGroup->getIsPublic()));
	}


	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
        // TODO: Should not forcefully delete
		// TODO: verify user dependencies
		return parent::delete($request, $response, $args);
	}


	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
			'description' => new Assert\Type(['type' => 'string']),
			'type' => new Assert\Type(['type' => 'string']),
		]);
	}


	protected static function getObjectName(): string
	{
		return 'userGroup';
	}


	protected static function getRepositoryClassName(): string
	{
		return UserGroupRepository::Class;
	}

    /**
     * @param array $userGroups
     * @return int|null
     * @throws InvalidAuthenticationException
     * @throws NonExistingObjectException
     */
    public function hasAccessToObject(array $userGroups): ?int
    {
        $rootRouteParent = self::getRootParent();
        //if there is no id, it means GET LIST was requested.
        if (is_null($rootRouteParent['id'])) {
            return null;
        }
        if ($rootRouteParent['type'] == 'userGroups') {
            /** @var UserGroup $group */
            $group = $this->getObjectViaORM(UserGroup::class, $rootRouteParent['id']);
            if (array_key_exists($group->getId(), $userGroups['group_wise']) || $group->getIsPublic())
            {
                return $group->getId();
            } else {
                throw new InvalidAuthenticationException("You cannot access this resource.",
                    "Not a member of the group.");
            }
        }
        throw new InvalidAuthenticationException('','');
    }

    public function getAccessFilter(array $userGroups): ?array
    {
        if ($this->userPermissions['platform_wise'] == User::ADMIN){
            return [];
        }
        $dql = "g.id";
        $accObj = $this->orm->getRepository(UserGroup::class)
            ->matching(Criteria::create()->where(Criteria::expr()->eq('isPublic', true)))
            ->map(function (UserGroup $group) { return $group->getId();})->toArray();
        foreach (array_flip($accObj) as $id => $trash) {
            $userGroups[$id] = $dql;
        }
        return array_map(function () use ($dql) { return $dql; }, $userGroups);
    }

    public function addUsers(Request $request, Response $response, ArgumentParser $args): Response
    {
        $this->isAuthorized($request->getAttribute('oauth_user_id'));
        $body = new ArgumentParser($request->getParsedBody());
        if ($body->hasKey('emails')) {
            $this->validate($body, new Assert\Collection([
                'emails' => new Assert\Type(['type' => 'array'])]));
            $emails = $body->getArray('emails');
            /** @var User[] $usersToAdd */
            $usersToAdd = $this->orm->getRepository(User::class)->findBy(['email' => $emails]);
            /** @var UserGroup $group */
            $group = $this->getObject($this->getModifyId($args));
            $usersToInviteCount = 0;
            $usersAddedCount = 0;
            foreach ($usersToAdd as $u) {
                if (in_array($u->getEmail(), $emails))
                {
                    $usersToInviteCount++;
                }
                if (!$group->getAllUsers()->contains($u)) {
                    $linkToMySpace = new UserGroupToUser();
                    $linkToMySpace->setUserId($u);
                    $linkToMySpace->setUserGroupId($group);
                    $linkToMySpace->setRoleId(User::SPECTATOR);
                    $this->orm->persist($linkToMySpace);
                    $usersAddedCount++;
                }
            }
            $this->orm->flush();
            return self::formatOk($response, [
                'users' => $emails,
                'added' => $usersAddedCount,
                'invited' => $usersToInviteCount]);
        }

    }

    /**
     * @param int|null $id
     * @throws InvalidAuthenticationException
     */
    private function isAuthorized(?int $id)
    {
        if(is_null($id)) {
            throw new InvalidAuthenticationException('User not authorized.',  'This endpoint is accessible' .
                ' only with valid token.');
        }
    }

    public function canAdd(int $role, int $id): bool
    {
        return false;
    }

    public function canEdit(int $role, int $id): bool
    {
        if ($role != User::OWNER_ROLE) {
            return false;
        }
        return true;
    }

    public function canDelete(int $role, int $id): bool
    {
        return $this->canEdit($role, $id);
    }
}
