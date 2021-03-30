<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use MathMLContentToLatex;

/**
 * @ORM\Entity
 * @ORM\Table(name="math_expression")
 */
class MathExpression
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
     * @ORM\Column(type="string", name="latex")
     */
    private $latex;

    /**
     * @var string
     * @ORM\Column(type="string", name="content_mathml")
     */
    private $contentMML;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLatex(): string
    {
        return $this->latex;
    }

    /**
     * @param string $latex
     */
    public function setLatex(string $latex): void
    {
        $this->latex = $latex;
    }

    /**
     * @return string
     */
    public function getContentMML(): string
    {
        return $this->contentMML;
    }

    /**
     * @param string $contentMML
     * @param bool $setLatexToo
     */
    public function setContentMML(string $contentMML, bool $setLatexToo = false): void
    {
        $this->contentMML = $contentMML;
        if ($setLatexToo){
            $this->setLatexFromCMML();
        }
    }

    public function setLatexFromCMML()
    {
        $this->latex = MathMLContentToLatex::convert($this->contentMML);
    }

}