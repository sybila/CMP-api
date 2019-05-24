<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_parameter")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelParameter implements IdentifiedObject
{

	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="parameters")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

	/**
	 * @ORM\ManyToOne(targetEntity="ModelReaction", inversedBy="parameters")
	 * @ORM\JoinColumn(name="model_reaction_id", referencedColumnName="id")
	 */
	protected $reactionId;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $value;

	/**
	 * @var boolean
	 * @ORM\Column(name="is_constant",type="integer")
	 */
	protected $isConstant;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelReactionItem", mappedBy="parameterId")
	 */
	protected $reactionItems;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelRule", mappedBy="parameterId")
	 */
	protected $rules;
	/**
	 * Get modelId
	 * @return integer|null
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * Set modelId
	 * @param integer $modelId
	 * @return ModelUnitDefinition
	 */
	public function setModelId($modelId): ModelParameter
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get reactionId
	 * @return integer|null
	 */
	public function getReactionId()
	{
		return $this->reactionId;
	}

	/**
	 * Set reactionId
	 * @param integer $reactionId
	 * @return ModelParameter
	 */
	public function setReactionId($reactionId): ModelParameter
	{
		$this->reactionId = $reactionId;
		return $this;
	}

	/**
	 * Get value
	 * @return integer
	 */
	public function getValue(): ?int
	{
		return $this->value;
	}

	/**
	 * Set value
	 * @param integer $value
	 * @return  ModelParameter
	 */
	public function setValue($value): ModelParameter
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * Get isConstant
	 * @return integer
	 */
	public function getIsConstant(): int
	{
		return $this->isConstant;
	}

	/**
	 * Set isConstant
	 * @param integer $isConstant
	 * @return  ModelParameter
	 */
	public function setIsConstant($isConstant): ModelParameter
	{
		$this->isConstant = $isConstant;
		return $this;
	}

	/**
	 * @return ModelReactionItem[]|Collection
	 */
	public function getReactionsItems(): Collection
	{
		return $this->reactionItems;
	}

	/**
	 * @return ModelRule[]|Collection
	 */
	public function getRules(): Collection
	{
		return $this->rules;
	}

}
