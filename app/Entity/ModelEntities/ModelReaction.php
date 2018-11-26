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
	 * @var int
	 * @ORM\ManyToOne(targetEntity="ModelCompartment", inversedBy="reactions")
	 * @ORM\JoinColumn(name="model_compartment_id", referencedColumnName="id")
	 */
	protected $compartmentId;

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
	 * @return string
	 * @ORM\Column(type="string")
	 */
	protected $rate;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="is_fast")
	 */
	protected $isFast;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="is_reversible")
	 */
	protected $isReversible;

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
	 * Get id
	 * @return integer
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * Get modelId
	 * @return integer
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
	 * @return integer
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
	 * Get name
	 * @return string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Set name
	 * @param string $name
	 * @return ModelReaction
	 */
	public function setName($name): ModelReaction
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
	public function setSbmlId($sbmlId): Model
	{
		$this->sbmlId = $sbmlId;
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

	/**
	 * Get isFast
	 * @return integer
	 */
	public function getIsFast()
	{
		return $this->isFast;
	}

	/**
	 * Set isFast
	 * @param integer $isFast
	 * @return ModelReaction
	 */
	public function setIsFast($isFast): ModelReaction
	{
		$this->isFast = $isFast;
		return $this;
	}

	/**
	 * Get rate
	 * @return string
	 */
	public function getRate(): ?string
	{
		return $this->rate;
	}

	/**
	 * Set rate
	 * @param string $rate
	 * @return ModelReaction
	 */
	public function setRate($rate): ModelReaction
	{
		$this->rate = $rate;
		return $this;
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

}



