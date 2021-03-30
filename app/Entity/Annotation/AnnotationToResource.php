<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="annotation_to_resource")
 */
class AnnotationToResource implements IdentifiedObject
{

    use Identifier;

    /**
     * @ORM\ManyToOne(targetEntity="AnnotationSource", inversedBy="annotatedResources",cascade={"remove"})
     * @ORM\JoinColumn(name="annotation_id", referencedColumnName="id")
     */
    private $annotation;

    /**
     * @ORM\Column(name="resource_id")
     */
    private $resourceId;


    /**
     * @ORM\Column(name="resource_type",type="annotable_obj_type")
     */
    private $resourceType;


    /**
     * AnnotationToResource constructor.
     * @param $annotation
     * @param $resourceId
     * @param $resourceType
     */
    public function __construct(AnnotationSource $annotation, $resourceId, $resourceType)
    {
        $this->annotation = $annotation;
        $this->resourceId = $resourceId;
        $this->resourceType = $resourceType;
    }


    /**
     * @return AnnotationSource
     */
    public function getAnnotation(): AnnotationSource
    {
        return $this->annotation;
    }

    /**
     * @param mixed $annotation
     */
    public function setAnnotation($annotation): void
    {
        $this->annotation = $annotation;
    }

    /**
     * @return int
     */
    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    /**
     * @param mixed $resourceId
     */
    public function setResourceId($resourceId): void
    {
        $this->resourceId = $resourceId;
    }


    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @param mixed $resourceType
     */
    public function setResourceType($resourceType): void
    {
        $this->resourceType = $resourceType;
    }



}