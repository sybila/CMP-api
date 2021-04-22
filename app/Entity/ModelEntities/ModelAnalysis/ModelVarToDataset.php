<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="model_variable_to_dataset")
 */
class ModelVarToDataset implements IdentifiedObject
{
    use Identifier;

    /**
     * @ORM\Column(type="string", name="var_type")
     */
    private $varType;

    /**
     * @ORM\ManyToOne(targetEntity="ModelParameter", inversedBy="datasets")
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id")
     */
    private $parameter;

    /**
     * @ORM\ManyToOne(targetEntity="ModelCompartment", inversedBy="inDatasets")
     * @ORM\JoinColumn(name="compartment_id", referencedColumnName="id")
     */
    private $compartment;

    /**
     * @ORM\ManyToOne(targetEntity="ModelSpecie", inversedBy="inDatasets")
     * @ORM\JoinColumn(name="species_id", referencedColumnName="id")
     */
    private $species;

    /**
     * @ORM\ManyToOne(targetEntity="ModelDataset", inversedBy="varsToDataset")
     * @ORM\JoinColumn(name="model_dataset_id", referencedColumnName="id")
     */
    private $dataset;

    /**
     * @ORM\Column(type="string")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Unit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id")
     */
    private $unit;

    /**
     * @ORM\ManyToOne(targetEntity="Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    private $attribute;

    /**
     * @ORM\Column(type="float", name="range_from")
     */
    private $rangeFrom;

    /**
     * @ORM\Column(type="float", name="range_to")
     */
    private $rangeTo;

    /**
     * @return mixed
     */
    public function getVarType()
    {
        return $this->varType;
    }

    /**
     * @param mixed $varType
     */
    public function setVarType($varType): void
    {
        $this->varType = $varType;
    }

    /**
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param mixed $parameter
     */
    public function setParameter($parameter): void
    {
        $this->parameter = $parameter;
    }

    /**
     * @return mixed
     */
    public function getCompartment()
    {
        return $this->compartment;
    }

    /**
     * @param mixed $compartment
     */
    public function setCompartment($compartment): void
    {
        $this->compartment = $compartment;
    }

    /**
     * @return mixed
     */
    public function getSpecies()
    {
        return $this->species;
    }

    /**
     * @param mixed $species
     */
    public function setSpecies($species): void
    {
        $this->species = $species;
    }

    /**
     * @return mixed
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    /**
     * @param mixed $dataset
     */
    public function setDataset($dataset): void
    {
        $this->dataset = $dataset;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit): void
    {
        $this->unit = $unit;
    }

    public function getModelVar(): ?IdentifiedObject
    {
        switch ($this->varType){
            case "compartment":
                return $this->getCompartment();
            case "species":
                return $this->getSpecies();
            case "parameter":
                return $this->getParameter();
            default:
                return null;
        }
    }

}