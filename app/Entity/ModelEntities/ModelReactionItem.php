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
	use SBase;

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
	 * @return double
	 */
	public function getStoichiometry(): ?float
	{
		return $this->stoichiometry;
	}

	/**
	 * Set stoichiometry
	 * @param double $stoichiometry
	 * @return ModelReactionItem
	 */
	public function setStochiometry($stoichiometry): ModelReactionItem
	{
		$this->stoichiometry = $stoichiometry;
		return $this;
	}

}
