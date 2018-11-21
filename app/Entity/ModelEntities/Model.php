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
 * @ORM\Table(name="model")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Model implements IdentifiedObject
{


	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="user_id")
	 */
	protected $userId;

	/**
	 * @var int
	 * @ORM\Column(type="integer", name="approved_id")
	 */
	protected $approvedId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $description;

	/**
	 * @var string
	 * @ORM\Column (type="string")
	 */
	protected $status;


	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelCompartment", mappedBy="modelId")
	 */
	protected $compartments;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelReaction", mappedBy="modelId")
	 */
	protected $reactions;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="ModelParameter", mappedBy="modelId")
	 */
	protected $parameters;

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
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Model
	 */
	public function setName($name): Model
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get userId
	 *
	 * @return integer
	 */
	public function getUserId(): ?int
	{
		return $this->userId;
	}


	/**
	 * Set userId
	 *
	 * @param int $userId
	 *
	 * @return Model
	 */
	public function setUserId($userId): Model
	{
		$this->userId = $userId;
		return $this;
	}

	/**
	 * Get approvedId
	 *
	 * @return integer
	 */
	public function getApprovedId(): ?int
	{
		return $this->approvedId;
	}


	/**
	 * Set approvedId
	 *
	 * @param int $approvedId
	 *
	 * @return Model
	 */
	public function setApprovedId($approvedId): Model
	{
		$this->approvedId = $approvedId;
		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return Model
	 */
	public function setDescription($description): Model
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function getStatus(): ?string
	{
		return $this->status;
	}

	/**
	 * Set status
	 *
	 * @param string $status
	 *
	 * @return Model
	 */
	public function setStatus($status): Model
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return Compartment[]|Collection
	 */
	public function getCompartments(): Collection
	{
		return $this->compartments;
	}

	/**
	 * @return Reaction[]|Collection
	 */
	public function getReactions(): Collection
	{
		return $this->reactions;
	}

}
