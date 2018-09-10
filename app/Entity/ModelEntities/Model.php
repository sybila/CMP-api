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
	 * @var string
	 * @ORM\Column (type="string")
	 */
	protected $solver;

	/**
	 * @ORM\OneToMany(targetEntity="Compartment", mappedBy="model")
	 * @ORM\JoinTable(name="model_compartment",
	 *     joinColumns={@ORM\JoinColumn(name="modelId")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="compartmentId")}
	 * )
	 * @var ArrayCollection
	 */
	protected $compartments;

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
	public function setName($name)
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
	public function setUserId($userId)
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
	public function setApprovedId($approvedId)
	{
		$this->approvedId = $approvedId;
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
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * Get solver
	 *
	 * @return string
	 */
	public function getSolver(): ?string
	{
		return $this->solver;
	}


	/**
	 * Set solver
	 *
	 * @param string $solver
	 *
	 * @return Model
	 */
	public function setSolver($solver)
	{
		$this->solver = $solver;
		return $this;
	}


	/**
	 * @return Compartment[]|Collection
	 */
	public function getCompartments(): Collection
	{
		return $this->compartments;
	}

}
