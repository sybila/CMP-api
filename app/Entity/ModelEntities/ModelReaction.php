<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_reaction")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelReaction implements IdentifiedObject
{
	use SBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

	/**
	 * @ORM\ManyToOne(targetEntity="ModelCompartment", inversedBy="reactions")
	 * @ORM\JoinColumn(name="model_compartment_id", referencedColumnName="id")
	 */
	protected $compartmentId;

    /**
     * @ORM\OneToOne(targetEntity="MathExpression", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="rate", referencedColumnName="id")
     */
	protected $rate;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="is_reversible")
	 */
	protected $isReversible;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelParameter", mappedBy="reactionId", cascade={"persist"})
	 */
	protected $parameters;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelReactionItem", mappedBy="reactionId", cascade={"persist"})
	 */
	protected $reactionItems;

	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="ModelFunction", mappedBy="reactionId", cascade={"persist"})
	 */
	protected $functions;

	/**
	 * Get model
	 * @return IdentifiedObject
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * Set modelId
	 * @param integer $modelId
	 * @return ModelReaction
	 */
	public function setModelId($modelId): ModelReaction
	{
		$this->modelId = $modelId;
		return $this;
	}

    /**
     * Get compartmentId
     * @return IdentifiedObject
     */
	public function getCompartmentId()
	{
		return $this->compartmentId;
	}

	/**
	 * Set compartmentId
	 * @param integer $compartmentId
	 * @return ModelReaction
	 */
	public function setCompartmentId($compartmentId): ModelReaction
	{
		$this->compartmentId = $compartmentId;
		return $this;
	}

	/**
	 * Get isReversible
	 * @return integer
	 */
	public function getIsReversible()
	{
		return $this->isReversible;
	}

	/**
	 * Set isReversible
	 * @param integer $isReversible
	 * @return ModelReaction
	 */
	public function setIsReversible($isReversible): ModelReaction
	{
		$this->isReversible = $isReversible;
		return $this;
	}


	public function getRate()
	{
		return $this->rate;
	}


	public function setRate($rate)
	{
		$this->rate = $rate;
	}

	/**
	 * @return Collection[] ModelFunction
	 */
	public function getFunctions(): Collection
	{
		return $this->functions;
	}

	/**
	 * @return Collection[] ReactionItem
	 */
	public function getReactionItems(): Collection
	{
		return $this->reactionItems;
	}

	/**
	 * @return Collection[] Parameter
	 */
	public function getParameters(): Collection
	{
		return $this->parameters;
	}

}



