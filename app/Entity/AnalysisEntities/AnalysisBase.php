<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait AnalysisBase
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer|null
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $annotation;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string) $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get id
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


    /**
     * Get annotation
     * @return string
     */
    public function getAnnotation()
    {
        return (string) $this->annotation;
    }

    /**
     * Set annotation
     * @param string|null $annotation
     */
    public function setAnnotation(?string $annotation): void
    {
        $this->annotation = $annotation;
    }

}