<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_graphset")
 */
class ExperimentGraphset implements IdentifiedObject
{

    use Identifier;

    /**
     * @ORM\OneToMany(targetEntity="ExpVarToGraphset", mappedBy="graphset", cascade={"persist", "remove"})
     */
    private $varToGraphset;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var Experiment $experiment
     * @ORM\ManyToOne(targetEntity="Experiment", inversedBy="graphset")
     * @ORM\JoinColumn(name="experiment_id", referencedColumnName="id")
     */
    private $experiment;


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getVarToGraphset()
    {
        return $this->varToGraphset;
    }

    /**
     * @param array $vars
     */
    public function setVarsToGraphset(array $vars): void
    {
        $this->varToGraphset = new ArrayCollection();
        foreach ($vars as $gVar){
            $rel = new ExpVarToGraphset();
            $rel->setGraphset($this);
            $rel->setVisualize($gVar['visualize']);
            $rel->setExpVar($this->experiment->getVariables()->filter(function (ExperimentVariable $var) use ($gVar){
                return $var->getName() === $gVar['name'];
            })->current());
            $this->varToGraphset->add($rel);
        }
    }

    /**
     * @return mixed
     */
    public function getExperiment()
    {
        return $this->experiment;
    }

    /**
     * @param mixed $experiment
     */
    public function setExperiment($experiment): void
    {
        $this->experiment = $experiment;
    }




}