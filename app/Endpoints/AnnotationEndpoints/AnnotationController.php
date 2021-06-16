<?php


namespace App\Controllers;

use App\Entity\AnnotatedEntity;
use App\Entity\AnnotationSource;
use App\Entity\AnnotationToResource;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnnotationSourceRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AnnotationSourceController
 * @package App\Controllers
 * @author Radoslav Doktor 433286@mail.muni.cz
 */
class AnnotationSourceController extends MultiParentedRepoController
{
    /** Type of the object that the annotation belongs to
     * @var string $annotatedObjType
     */
    private $annotatedObjType = '';


    /**
     * Detail on annotation not allowed
     */
    protected static function getAllowedSort(): array
    {
        return [];
    }

    protected static function getRepositoryClassName(): string
    {
        return AnnotationSourceRepository::class;
    }

    protected static function getObjectName(): string
    {
        return AnnotationSource::class;
    }

    /**
     * Detail on annotation not allowed
     * @param IdentifiedObject $object
     * @return array
     */
    protected function getData(IdentifiedObject $object): array
    {
        return [];
    }

    protected function setData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var AnnotationSource $object */
        !$body->hasKey('link') ?
            $object->setLink('http://identifiers.org/' . $body['sourceNamespace'] .'/'. $body['sourceIdentifier']) :
            $object->setLink($body->getString('link'));
        !$body->hasKey('qualifier') ?: $object->setQualifier($body->getString('qualifier'));
        !$body->hasKey('sourceNamespace') ?: $object->setSourceNamespace($body->getString('sourceNamespace'));
        !$body->hasKey('sourceIdentifier') ?: $object->setSourceIdentifier($body->getString('sourceIdentifier'));
    }

    protected function getAnnotableType(): string
    {
        return $this->annotatedObjType;
    }

    /**
     * @param string $annotatedObjType
     */
    public function setAnnotatedObjType(string $annotatedObjType): void
    {
        $this->annotatedObjType = $annotatedObjType;
    }


    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        $url = $body->hasKey('link');
        $uri = ($body->hasKey('sourceNamespace') && $body->hasKey('sourceIdentifier'));
        if (!$url && !$uri) {
            throw new MissingRequiredKeyException('(link) or (sourceNamespace and sourceIdentifier)');
        }
        $object = new AnnotationSource;
        $object->annotate($object, $this->getAnnotableType(), $this->repository->getParent()->getId());
        return $object;
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        /** @var AnnotationSource $object */
        if ($object->getLink() === null){
            throw new MissingRequiredKeyException('link');
        }
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'link' => new Assert\Url(),
        ]);
    }

    protected function getParentObjectInfo(): ParentObjectInfo
    {
        return new ParentObjectInfo('obj-id','obj');
    }

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        /** @var AnnotationSource $child */
        $count = $child->getAnnotatedResources()->filter(function (AnnotationToResource $rel) use ($parent){
            return ($rel->getResourceId() == $parent->getId()) &&
                ($rel->getResourceType() === $this->annotatedObjType);
        })->count();
        if (!($count>0)){
            throw new WrongParentException($this->annotatedObjType,$parent->getId(),'annotation',$child->getId());
        }
    }

    protected function getParentObject(ArgumentParser $args): IdentifiedObject
    {
        $info = static::getParentObjectInfo();
        try {
            $id = $args->get($info->parentIdRoutePlaceholder);
            $this->annotatedObjType = AnnotatedEntity::$routeObjToEntityClass[$args->get($info->parentEntityClass)];
        } catch (Exception $e) {
            throw new MissingRequiredKeyException($e->getMessage());
        }
        return $this->getObjectViaORM($this->annotatedObjType, $id);
    }

    public function canList(?int $role, ?int $id): bool
    {
        return true;
    }

    public function canDetail(?int $role, ?int $id): bool
    {
        return true;
    }
}