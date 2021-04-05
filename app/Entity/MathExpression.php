<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use DOMDocument;
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

    public function getModelComponents(Model $model)
    {
        $dom = new DOMDocument;
        $dom->loadXML($this->contentMML);
        $cIdentifiers = $dom->getElementsByTagName('ci');
        $compartments = $model->getCompartments();
        $params = $model->getParameters();
        $reactions = $model->getReactions();
        $fnDefs = $model->getFunctionDefinitions();
        $compIn = [];
        $specIn = [];
        $paraIn = [];
        $reactIn = [];
        $fnDefIn = [];
        $notFound = [];
        foreach ($cIdentifiers as $ci) {
            $found = false;
            $pCi = preg_replace('/\s+/', '', $ci->nodeValue);
            if (!$found) {
                foreach ($compartments as $mc) {
                    if ($mc->getAlias() === $pCi) {
                        $compIn[$mc->getAlias()] = [
                            'id' => $mc->getId(),
                            'alias' => $mc->getAlias(),
                            'size' => $mc->getSize()];
                        $found = true;
                    }
                    foreach ($mc->getSpecies() as $spec) {
                        if ($spec->getAlias() === $pCi) {
                            $specIn[$spec->getAlias()] = [
                                'id' => $spec->getId(),
                                'alias' => $spec->getAlias(),
                                //FIXME initial value for specie is nowhere
                                ];
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        break;
                    }
                }
            }
            if (!$found) {
                foreach ($params as $mpar) {
                    if ($mpar->getAlias() === $pCi) {
                        $paraIn[$mpar->getAlias()] = [
                            'id' => $mpar->getId(),
                            'alias' => $mpar->getAlias(),
                            'value' => $mpar->getValue()];
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                foreach ($reactions as $mreact) {
                    if ($mreact->getAlias() === $pCi) {
                        $reactIn[$mreact->getAlias()] = [
                            'id' => $mreact->getId(),
                            'alias' => $mreact->getAlias(),
                            'rate' => $mreact->getRate()];
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                foreach ($fnDefs as $fnDef) {
                    if ($fnDef->getAlias() === $pCi) {
                        $fnDefIn[$fnDef->getAlias()] = [
                            'id' => $fnDef->getId(),
                            'alias' => $fnDef->getAlias(),
                            'function' => $fnDef->getExpression()->getLatex(),
                            'args' => $fnDef->getArguments()];
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                array_push($notFound, $pCi);
            }
        }
        return [
            'expandedLatex' => empty($fnDefIn) ? $this->getLatex() : $this->substituteFunction($fnDefIn),
            'components' =>
                ['compartments' => $compIn,
                    'species' => $specIn,
                    'parametes' => $paraIn,
                    'reaction' => $reactIn,
                    'functionDefinitions' => $fnDefIn,
                    'notFound' => array_unique($notFound)]];
    }

    protected function substituteFunction(array $defs): string
    {
        $latex = $this->getLatex();
        foreach ($defs as $alias => $fn){
            $needle = '\mathrm{' . addcslashes($alias, '_') . '}';
            while ($beforeMatch = strstr($latex, $needle ,true)){
                $afterMatch = substr($latex, strlen($beforeMatch . $needle));
                $parEnd = $this->getSome($afterMatch) - 1;
                $remainingExpression = substr($afterMatch, $parEnd + 2);
                $in = substr($afterMatch, 1, $parEnd);
                $substitutions = explode(',', $in);
                $broken = explode( '\mapsto', $fn['function']);
                $vars = $fn['args'];
                $i = 0;
                foreach ($substitutions as $sub)
                {
                    $broken[1] = str_replace($vars[$i], $sub, $broken[1]);
                    $i++;
                }
                $latex = $beforeMatch . '(' . substr($broken[1], 0, strlen($broken[1]) - 1) . ')'. $remainingExpression;
            }
        }
        return $latex;
    }

    protected function getSome(string $str)
    {
        $par = 0;
        for ($i = 0; $i <= strlen($str); $i++) {
            if ($str[$i] === '('){
                $par++;
            }
            if ($str[$i] === ')'){
                if ($par == 0) {
                    break;
                }
                $par--;
                if ($par == 0){
                    return $i;
                }
            }
        }
        return -1;
    }

}