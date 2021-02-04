<?php
namespace App\Entity;

use App\Entity\Identifier;
use App\Entity\IdentifiedObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="annotation_to_resource")
 */
class AnnotationToResource implements IdentifiedObject
{

    use Identifier;

    /**
     * @ORM\ManyToOne(targetEntity="AnnotationSource", inversedBy="annotatedResources")
     * @ORM\JoinColumn(name="annotation_id", referencedColumnName="id")
     */
    private $annotation;

    /**
     * @ORM\Column(name="resource_id")
     */
    private $resourceId;

    /**
     * @ORM\OneToOne(targetEntity="AnnotableObject")
     * @ORM\JoinColumn(name="resource_type", referencedColumnName="id")
     */
    private $resourceType;


    /**
     * AnnotationToResource constructor.
     * @param $annotation
     * @param $resourceId
     * @param $resourceType
     */
    public function __construct(AnnotationSource $annotation, $resourceId, AnnotableObject $resourceType)
    {
        $this->annotation = $annotation;
        $this->resourceId = $resourceId;
        $this->resourceType = $resourceType;
    }


    /**
     * @return AnnotationSource
     */
    public function getAnnotationId()
    {
        return $this->annotation;
    }

    /**
     * @param mixed $annotationId
     */
    public function setAnnotationId($annotationId): void
    {
        $this->annotationId = $annotationId;
    }

    /**
     * @return int
     */
    public function getResourceId()
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

    /**
     * @return AnnotableObject
     */
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