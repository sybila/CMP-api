<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_var_to_graphset")
 */
class ExpVarToGraphset implements IdentifiedObject
{
    use Identifier;

    /**
     * @ORM\ManyToOne(targetEntity="ExperimentVariable", inversedBy="graphsets")
     * @ORM\JoinColumn(name="exp_var_id", referencedColumnName="id")
     */
    private $expVar;

    /**
     * @ORM\ManyToOne(targetEntity="ExperimentGraphset", inversedBy="varToGraphset")
     * @ORM\JoinColumn(name="exp_graphset_id", referencedColumnName="id")
     */
    private $graphset;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visualize;

    /**
     * @return mixed
     */
    public function getExpVar()
    {
        return $this->expVar;
    }

    /**
     * @param mixed $expVar
     */
    public function setExpVar($expVar): void
    {
        $this->expVar = $expVar;
    }

    /**
     * @return mixed
     */
    public function getGraphset()
    {
        return $this->graphset;
    }

    /**
     * @param mixed $graphset
     */
    public function setGraphset($graphset): void
    {
        $this->graphset = $graphset;
    }

    /**
     * @return mixed
     */
    public function getVisualize()
    {
        return $this->visualize;
    }

    /**
     * @param mixed $visualize
     */
    public function setVisualize($visualize): void
    {
        $this->visualize = $visualize;
    }



}