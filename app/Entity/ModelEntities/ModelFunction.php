<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get name
	 * @return null|string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Set name
	 * @param string $name
	 * @return ModelUnitToDefinition
	 */
	public function setName($name): ModelFunction
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get reactionId
	 * @return integer
	 */
	public function getReactionId()
	{
		return $this->reactionId;
	}

	/**
	 * Set reactionId
	 * @param integer $reactionId
	 * @return ModelReactionItem
	 */
	public function setReactionId($reactionId): ModelFunction
	{
		$this->reactionId = $reactionId;
		return $this;
	}

	/**
	 * Get formula
	 * @return null|string
	 */
	public function getFormula(): ?string
	{
		return $this->formula;
	}

	/**
	 * Set function
	 * @param string $function
	 * @return ModelUnitToDefinition
	 */
	public function setFormula($formula): ModelFunction
	{
		$this->formula = $formula;
		return $this;
	}

}