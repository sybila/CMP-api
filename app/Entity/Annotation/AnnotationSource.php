<?php
namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AnnotationSource
 * @ORM\Entity
 * @ORM\Table(name="annotation")
 */
class AnnotationSource implements IdentifiedObject
{
    use Identifier;

    /**
     * @ORM\Column(name="source")
     */
    protected $source;

    /**
     * @ORM\Column(name="source_id",nullable=true)
     */
    protected $sourceId;

    /**
     * @ORM\Column(name="link",type="string")
     */
    protected $link;

    /**
     * @ORM\OneToMany(targetEntity="AnnotationToResource", mappedBy="annotation", cascade={"persist", "remove"})
     */
    private $annotatedResources;


    public function getAnnotatedResources()
    {
        return $this->annotatedResources;
    }

    public function annotate($annotation, $resType, $resId)
    {
        $newAnnotation = new AnnotationToResource($annotation, $resId, $resType);
        $this->annotatedResources = new ArrayCollection();
        $this->annotatedResources->add($newAnnotation);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source): void
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * @param mixed $sourceId
     */
    public function setSourceId($sourceId): void
    {
        $this->sourceId = $sourceId;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link): void
    {
        $this->link = $link;
    }

}
