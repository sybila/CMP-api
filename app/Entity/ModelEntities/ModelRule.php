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
abstract class ModelRule implements IdentifiedObject
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;


	/**
	 * @var integer
	 * @ORM\Column(type="integer", name="model_id")
	 */
	private $modelId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $type;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $equation;

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
	 * @return integer
	 */
	public function getModelId(): ?int
	{
		return $this->modelId;
	}

	/**
	 * Set name
	 *
	 * @param string $type
	 *
	 * @return Rule
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Set name
	 *
	 * @param string $equation
	 *
	 * @return Rule
	 */
	public function setEquation($equation)
	{
		$this->equation = $equation;
		return $this;
	}

}

class AlgebraicRule extends ModelRule
{

}

class AssignmentRule extends ModelRule
{

}

class RateRule extends ModelRule
{

}