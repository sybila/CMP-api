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

	protected $localParameters;


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
	public function setReactionId($reactionId): ModelReactionItem
	{
		$this->reactionId = $reactionId;
		return $this;
	}


	/**
	 * Get specieId
	 *
	 * @return integer
	 */
	public function getSpecieId()
	{
		return $this->specieId;
	}

	/**
	 * Set specieId
	 *
	 * @param integer $specieId
	 *
	 * @return ModelReactionItem
	 */
	public function setSpecieId($specieId): ModelReactionItem
	{
		$this->specieId = $specieId;
		return $this;
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
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return ModelUnitToDefinition
	 */
	public function setName($name): ModelReactionItem
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get type
	 *
	 * @return null|string
	 */
	public function getType(): ?string
	{
		return $this->type;
	}


	/**
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return ModelUnitToDefinition
	 */
	public function setType($type): ModelReactionItem
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Get isGlobal
	 *
	 * @return integer
	 */
	public function getIsGlobal(): ?int
	{
		return $this->isGlobal;
	}

	/**
	 * Set isGlobal
	 *
	 * @param integer $isGlobal
	 *
	 * @return ModelReactionItem
	 */
	public function setIsGlobal($isGlobal): ModelReactionItem
	{
		$this->isGlobal = $isGlobal;
		return $this;
	}

	/**
	 * Get value
	 *
	 * @return integer
	 */
	public function getValue(): ?int
	{
		return $this->value;
	}

	/**
	 * Set value
	 *
	 * @param integer $value
	 *
	 * @return ModelReactionItem
	 */
	public function setValue($value): ModelReactionItem
	{
		$this->value = $value;
		return $this;
	}


	/**
	 * Get stoichiometry
	 *
	 * @return integer
	 */
	public function getStoichiometry(): ?int
	{
		return $this->stoichiometry;
	}

	/**
	 * Set stoichiometry
	 *
	 * @param integer $stoichiometry
	 *
	 * @return ModelReactionItem
	 */
	public function setStochiometry($stoichiometry): ModelReactionItem
	{
		$this->stoichiometry = $stoichiometry;
		return $this;
	}


}
