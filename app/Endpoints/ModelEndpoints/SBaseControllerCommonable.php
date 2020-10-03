<?php

use App\Entity\Authorization\User;
use App\Entity\IdentifiedObject;
use App\Entity\Model;
use App\Entity\SBase;
use App\Exceptions\InvalidAuthenticationException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

trait SBaseControllerCommonable
{
    protected function getSBaseData(IdentifiedObject $object): array
    {
        /** @var SBase $object*/
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'sbmlId' => $object->getSbmlId(),
            'sboTerm' => $object->getSboTerm(),
            'notes' => $object->getNotes(),
            'annotation' => $object->getAnnotation()
        ];
    }

    protected function setSBaseData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var SBase $object */
        !$body->hasKey('name') ? $object->setName($body->getString('sbmlId')) : $object->setName($body->getString('name'));
        !$body->hasKey('sbmlId') ?: $object->setSbmlId($body->getString('sbmlId'));
        !$body->hasKey('sboTerm') ?: $object->setSboTerm($body->getString('sboTerm'));
        !$body->hasKey('notes') ?: $object->setNotes($body->getString('notes'));
        !$body->hasKey('annotation') ?: $object->setAnnotation($body->getString(('annotation')));
    }

    protected function getSBaseValidator(): array
    {
        return [
            'name' => new Assert\Type(['type' => 'string']),
            'sbmlId' => new Assert\Type(['type' => 'string']),
            'sboTerm' => new Assert\Type(['type' => 'string']),
            'notes' => new Assert\Type(['type' => 'string']),
            'annotation' => new Assert\Type(['type' => 'string']),
        ];
    }

    public function hasAccessToObject(array $userGroups): ?int
    {
        $rootRouteParent = self::getRootParent();
        if (is_null($rootRouteParent['id'])) {
            return null;
        }
        if ($rootRouteParent['type'] == 'models') {
            /** @var Model $routeParentObject */
            $routeParentObject = $this->getObjectViaORM(Model::class, $rootRouteParent['id']);
            if(array_key_exists($routeParentObject->getGroupId(), $userGroups)) {
                return $routeParentObject->getGroupId();
            } else {
                throw new InvalidAuthenticationException("You cannot access this resource.",
                    "Not a member of the group.");
            }
        }
        else {
            throw new WrongParentException($rootRouteParent['type'], $rootRouteParent['type'] == 'models',
            $this->getObjectName(), 'any id');
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