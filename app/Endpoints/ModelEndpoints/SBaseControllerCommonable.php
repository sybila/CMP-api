<?php

namespace App\Controllers;

use App\Entity\AnnotableObject;
use App\Entity\AnnotationSource;
use App\Entity\AnnotationToResource;
use App\Entity\Authorization\User;
use App\Entity\IdentifiedObject;
use App\Entity\KnownAnnotableObjType;
use App\Entity\Model;
use App\Entity\SBase;
use App\Exceptions\InvalidAuthenticationException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Doctrine\Common\Collections\Criteria;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Radoslav Doktor
 * Trait SBaseControllerCommonable
 * @package App\Controllers
 */
trait SBaseControllerCommonable
{
    protected function getSBaseData(IdentifiedObject $object): array
    {
        /** @var SBase $object*/
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'alias' => $object->getAlias(),
            'sboTerm' => $object->getSboTerm(),
            'notes' => $object->getNotes(),
            'annotations' => $object->getAnnotations($this->orm)
                ->map(function (AnnotationSource $ann) {
                    return ['id' => $ann->getId(),
                        'link' => $ann->getLink()];
                })->toArray()
        ];
    }

    protected function setSBaseData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var SBase $object */
        !$body->hasKey('name') ?: $object->setName($body->getString('name'));
        !$body->hasKey('alias') ?: $object->setAlias($body->getString('alias'));
        !$body->hasKey('sboTerm') ?: $object->setSboTerm($body->getString('sboTerm'));
        !$body->hasKey('notes') ?: $object->setNotes($body->getString('notes'));
    }

    protected function getSBaseValidator(): array
    {
        return [
            'name' => new Assert\Type(['type' => 'string']),
            'alias' => new Assert\Type(['type' => 'string']),
            'sboTerm' => new Assert\Type(['type' => 'string']),
            'notes' => new Assert\Type(['type' => 'string']),
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
            if($routeParentObject->isPublic()){
                return null;
            }
            if(array_key_exists($routeParentObject->getGroupId(), $userGroups)) {
                return $routeParentObject->getGroupId();
            } else {
                throw new InvalidAuthenticationException("You cannot access this resource.",
                    "You have tried to access a private resource that does not belong to any of your groups");
            }
        }
        else {
            throw new WrongParentException($rootRouteParent['type'], $rootRouteParent['type'] == 'models',
            $this->getObjectName(), 'any id');
        }
    }

    public function getAccessFilter(array $userGroups): ?array
    {
        if ($this->userPermissions['platform_wise'] == User::ADMIN){
            return [];
        }
        $dql = "m.groupId";
        $dql_array = (array_map(function () use ($dql) { return $dql; }, $userGroups));
        $dql_array['true'] = "m.isPublic";
        return $dql_array;
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

    public function deleteAnnotations($objId)
    {
        $entity = $this->getObject($objId);
        foreach ($entity->getAnnotations($this->orm) as $annotation){
            $this->orm->remove($annotation);
        }
    }

}