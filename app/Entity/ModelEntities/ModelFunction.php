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
 * @ORM\Table(name="model_function")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelFunction implements IdentifiedObject
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;



	/**
	 * @var int
	 * @ORM\ManyToOne(targetEntity="ModelReaction", inversedBy="reactionItems")
	 * @ORM\JoinColumn(name="model_reaction_id", referencedColumnName="id")
	 */
	protected $reactionId;


	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $name;


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
	 * Get name
	 *
	 * @return null|string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Get reactionId
	 *
	 * @return integer
	 */
	public function getReactionId()
	{
		return $this->reactionId;
	}

	/**
	 * Set reactionId
	 *
	 * @param integer $reactionId
	 *
	 * @return ModelReactionItem
	 */
	public function setReactionId($reactionId): ModelFunction
	{
		$this->reactionId = $reactionId;
		return $this;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return ModelCompartment
	 */
	public function setName($name): ModelFunction
	{
		$this->name = $name;
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
	 * @return ModelCompartment
	 */
	public function setFormula($formula): ModelFunction
	{
		$this->formula = $formula;
		return $this;
	}


}