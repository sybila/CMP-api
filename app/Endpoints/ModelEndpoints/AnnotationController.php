<?php


namespace App\Controllers;


use App\Entity\AnnotableObject;
use App\Entity\AnnotationSource;
use App\Entity\AnnotationToResource;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnnotationSourceRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

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
        !$body->hasKey('link') ?: $object->setLink($body->getString('link'));
    }

    protected function getAnnotableType(): AnnotableObject
    {
        /** @var AnnotableObject $type */
        $type = $this->orm->getRepository(AnnotableObject::class)
            ->findOneBy(['type' => $this->annotatedObjType]);
        return $type;
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        if (!$body->hasKey('link'))
            throw new MissingRequiredKeyException('link');
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
                ($rel->getResourceType()->getType() === $this->annotatedObjType);
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
            $this->annotatedObjType = $args->get($info->parentEntityClass);
        } catch (Exception $e) {
            throw new MissingRequiredKeyException($e->getMessage());
        }
        return $this->getObjectViaORM(AnnotableObject::KNOWN_TYPES[$this->annotatedObjType], $id);
    }
}