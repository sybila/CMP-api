<?php
namespace App\Entity;


use App\Exceptions\MissingRequiredKeyException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AnnotationSource
 * @ORM\Entity
 * @ORM\Table(name="annotation")
 * @author Radoslav Doktor 433286@mail.muni.cz
 */
class AnnotationSource implements IdentifiedObject
{
    use Identifier;

    /**
     * @ORM\Column(name="source")
     */
    protected $sourceNamespace;

    /**
     * @ORM\Column(name="source_id",nullable=true)
     */
    protected $sourceIdentifier;

    /**
     * @ORM\Column(name="link",type="string")
     */
    protected $link;

    /**
     * @ORM\Column(name="qualifier", type="string")
     */
    protected $qualifier;
    protected $possibleQualifiers = ["encodes","hasPart","hasProperty","hasVersion","is","isDescribedBy","isEncodedBy","isHomologTo",
        "isPartOf","isPropertyOf","isVersionOf","occursIn","hasTaxon", "isDerivedFrom","isInstanceOf","hasInstance"];
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
    public function getSourceNamespace()
    {
        return $this->sourceNamespace;
    }

    /**
     * @param mixed $sourceNamespace
     */
    public function setSourceNamespace($sourceNamespace): void
    {
        $this->sourceNamespace = $sourceNamespace;
    }

    /**
     * @return mixed
     */
    public function getSourceIdentifier()
    {
        return $this->sourceIdentifier;
    }

    /**
     * @param mixed $sourceIdentifier
     */
    public function setSourceIdentifier($sourceIdentifier): void
    {
        $this->sourceIdentifier = $sourceIdentifier;
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

    /**
     * @return mixed
     */
    public function getQualifier()
    {
        return $this->qualifier;
    }

    /**
     * @param mixed $qualifier
     */
    public function setQualifier($qualifier): void
    {
        if (in_array('qualifier',$this->possibleQualifiers)) {
            $this->qualifier = $qualifier;
        } else {
            /** @var string qualifier; if not set, 'occursIn' appears as the most general qualifier */
            $this->qualifier = $this->possibleQualifiers[2];
        }
    }



}
