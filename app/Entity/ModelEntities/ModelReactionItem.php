<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_reaction_item")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelReactionItem implements IdentifiedObject
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
	 * @ORM\ManyToOne(targetEntity="ModelParameter", inversedBy="reactionItems")
	 * @ORM\JoinColumn(name="model_parameter_id", referencedColumnName="id")
	 */
	protected $parameterId;

	/**
	 * @var int
	 * @ORM\ManyToOne(targetEntity="ModelReaction", inversedBy="reactionItems")
	 * @ORM\JoinColumn(name="model_reaction_id", referencedColumnName="id")
	 */
	protected $reactionId;

	/**
	 * @var int
	 * @ORM\ManyToOne(targetEntity="ModelSpecie", inversedBy="reactionItems")
	 * @ORM\JoinColumn(name="model_specie_id", referencedColumnName="id")
	 */
	protected $specieId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $type;

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
	 * @ORM\Column(type="integer", name="is_global")
	 */
	protected $isGlobal;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $value;

	/**
	 * @return string
	 * @ORM\Column(type="string")
	 */
	protected $stoichiometry;

	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
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
	 * @return ModelReactionItem
	 */
	public function setParameterId($parameterId): ModelReactionItem
	{
		$this->parameterId = $parameterId;
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
	public function setReactionId($reactionId): ModelReactionItem
	{
		$this->reactionId = $reactionId;
		return $this;
	}

	/**
	 * Get specieId
	 * @return integer
	 */
	public function getSpecieId()
	{
		return $this->specieId;
	}

	/**
	 * Set specieId
	 * @param integer $specieId
	 * @return ModelReactionItem
	 */
	public function setSpecieId($specieId): ModelReactionItem
	{
		$this->specieId = $specieId;
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
	public function setName($name): ModelReactionItem
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
	public function setSbmlId($sbmlId): ModelReactionItem
	{
		$this->sbmlId = $sbmlId;
		return $this;
	}

	/**
	 * Get type
	 * @return null|string
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * Set type
	 * @param string $type
	 * @return ModelUnitToDefinition
	 */
	public function setType($type): ModelReactionItem
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Get isGlobal
	 * @return integer
	 */
	public function getIsGlobal(): ?int
	{
		return $this->isGlobal;
	}

	/**
	 * Set isGlobal
	 * @param integer $isGlobal
	 * @return ModelReactionItem
	 */
	public function setIsGlobal($isGlobal): ModelReactionItem
	{
		$this->isGlobal = $isGlobal;
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
	public function setValue($value): ModelReactionItem
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * Get stoichiometry
	 * @return integer
	 */
	public function getStoichiometry(): ?int
	{
		return $this->stoichiometry;
	}

	/**
	 * Set stoichiometry
	 * @param integer $stoichiometry
	 * @return ModelReactionItem
	 */
	public function setStochiometry($stoichiometry): ModelReactionItem
	{
		$this->stoichiometry = $stoichiometry;
		return $this;
	}

}
