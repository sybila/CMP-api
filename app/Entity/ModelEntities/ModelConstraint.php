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
 * @ORM\Table(name="model_constraint")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelConstraint implements IdentifiedObject
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
	protected $message;


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
	 * @return ModelConstraint
	 */
	public function setModelId($modelId): ModelConstraint
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get message
	 *
	 * @return null|string
	 */
	public function getMessage(): ?string
	{
		return $this->message;
	}


	/**
	 * Set message
	 *
	 * @param string $message
	 *
	 * @return ModelConstraint
	 */
	public function setMessage($message): ModelConstraint
	{
		$this->message = $message;
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
	 * Set formula
	 *
	 * @param string $formula
	 *
	 * @return ModelConstraint
	 */
	public function setFormula($formula): ModelConstraint
	{
		$this->formula = $formula;
		return $this;
	}

}
