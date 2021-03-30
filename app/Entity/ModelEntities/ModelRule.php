<?php

namespace App\Entity;

use App\Exceptions\EntityClassificationException;
use App\Exceptions\EntityHierarchyException;
use App\Exceptions\EntityLocationException;
use App\Helpers\
{
	ChangeCollection, ConsistenceEnum
};
use App\Exceptions\EntityException;
use Consistence\Enum\InvalidEnumValueException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\Tests\StringClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_rule")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelRule implements IdentifiedObject
{
	use SBase;

	/**
     * @ORM\ManyToOne(targetEntity="Model", inversedBy="modelRules")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	private $modelId;

    /**
     * @ORM\ManyToOne(targetEntity="ModelCompartment", inversedBy="rules")
     * @ORM\JoinColumn(name="model_compartment_id", referencedColumnName="id")
     */
	private $compartmentId;

	/**
	 * @var integer
	 * @ORM\Column(type="integer", name="model_parameter_id")
	 */
	private $parameterId;

	/**
	 * @var integer
	 * @ORM\Column(type="integer", name="model_specie_id")
	 */
	private $specieId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $type;

    /**
     * @ORM\OneToOne(targetEntity="MathExpression", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="equation", referencedColumnName="id")
     */
	protected $expression;

	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get modelId
	 * @return integer
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * Set modelId
	 * @param integer $modelId
	 * @return ModelRule
	 */
	public function setModelId($modelId): ModelRule
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get parameterId
	 * @return integer
	 */
	public function getParameterId()
	{
		return $this->parameterId;
	}

	/**
	 * Set parameterId
	 * @param integer $parameterId
	 * @return ModelRule
	 */
	public function setParameterId($parameterId): ModelRule
	{
		$this->parameterId = $parameterId;
		return $this;
	}

	/**
	 * Get compartmentId
	 * @return integer
	 */
	public function getCompartmentId(): ?int
	{
		return $this->compartmentId;
	}

    /**
     * @param ModelCompartment $compartment
     */
    public function setCompartmentId(ModelCompartment $compartment): void
    {
        $this->compartmentId = $compartment;
    }


	/**
	 * Get specieId
	 * @return integer
	 */
	public function getSpecieId(): ?int
	{
		return $this->specieId;
	}

    /**
     * @param int $specieId
     */
    public function setSpecieId(int $specieId): void
    {
        $this->specieId = $specieId;
    }


	/**
	 * Get type
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set name
	 * @param string $type
	 * @return Rule
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}


	public function getExpression()
	{
		return $this->expression;
	}

	public function setExpression($expression)
	{
		$this->expression = $expression;
	}

}