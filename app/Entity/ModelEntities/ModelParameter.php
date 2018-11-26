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

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

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
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="sbml_id")
	 */
	private $sbmlId;

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
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

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
	 * Get reactionItemId
	 * @return integer|null
	 */
	public function getReactionItemId()
	{
		return $this->reactionItemId;
	}

	/**
	 * Set reactionItemId
	 * @param integer $reactionItemId
	 * @return ModelParameter
	 */
	public function setReactionItemId($reactionItemId): ModelParameter
	{
		$this->reactionItemId = $reactionItemId;
		return $this;
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
	public function setName($name): ModelParameter
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get sbmlId
	 * @return string
	 */
	public function getSbmlId()
	{
		return $this->sbmlId;
	}

	/**
	 * Set sbmlId
	 * @param string $sbmlId
	 * @return Model
	 */
	public function setSbmlId($sbmlId): ModelParameter
	{
		$this->sbmlId = $sbmlId;
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
	 * @return ModelReactionItem
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
	 * @return ModelCompartment
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
