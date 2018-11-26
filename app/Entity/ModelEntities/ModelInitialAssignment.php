<?php

namespace App\Entity;


use App\Exceptions\EntityClassificationException;
use App\Exceptions\EntityHierarchyException;
use App\Exceptions\EntityLocationException;
use App\Helpers\
{
	ChangeCollection, ConsistenceEnum
};
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\Tests\StringClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_initial_assignment")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelInitialAssignment implements IdentifiedObject
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;


	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $formula;


	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get modelId
	 *
	 * @return integer|null
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * Set modelId
	 *
	 * @param integer $modelId
	 *
	 * @return ModelCompartment
	 */
	public function setModelId($modelId): ModelCompartment
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get formula
	 *
	 * @return null|string
	 */
	public function getFormula(): ?string
	{
		return $this->formula;
	}

	/**
	 * Set function
	 *
	 * @param string $function
	 *
	 * @return ModelUnitToDefinition
	 */
	public function setFormula($formula): ModelFunction
	{
		$this->formula = $formula;
		return $this;
	}

}
