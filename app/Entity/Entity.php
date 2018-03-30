<?php

namespace App\Entity;

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

final class EntityStatus extends ConsistenceEnum
{
	const INACTIVE = 'inactive';
	const ACTIVE = 'active';
	const PENDING = 'pending';

	private static $toInt = [
		self::INACTIVE => 0,
		self::ACTIVE => 1,
		self::PENDING => 2,
	];

	public function toInt(): int
	{
		return self::$toInt[$this->getValue()];
	}

	public static function fromInt(int $value): EntityStatus
	{
		$key = array_search($value, self::$toInt, true);
		if ($key === false)
			throw new InvalidEnumValueException($value, array_values(self::$toInt));

		return self::get($key);
	}
}

/**
 * @ORM\Entity(repositoryClass="EntityRepository")
 * @ORM\EntityListeners({"EntityListener"})
 * @ORM\Table(name="ep_entity")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 * @ORM\DiscriminatorMap({0 = "AtomicState", 1 = "Compartment", 2 = "Complex", 3 = "Structure", 4 = "Atomic"})
 */
abstract class Entity
{
	use ChangeCollection;

	public static $classToType = [
		Compartment::class => 'compartment',
		Complex::class => 'complex',
		Structure::class => 'structure',
		Atomic::class => 'atomic',
		AtomicState::class => 'state',
	];

	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $code;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $description;

	/**
	 * @var int
	 * @ORM\Column(name="active",type="integer",nullable=true)
	 */
	protected $status;

	/**
	 * @var string
	 * @ORM\Column(name="type",type="string")
	 */
	protected $internalType;

	/**
	 * @ORM\ManyToMany(targetEntity="EntityClassification")
	 * @ORM\JoinTable(name="ep_entity_classification",
	 *     joinColumns={@ORM\JoinColumn(name="entityId")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="classificationId")}
	 * )
	 * @var ArrayCollection
	 */
	protected $classifications;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="EntityAnnotation", mappedBy="entity", cascade={"persist", "remove"})
	 */
	protected $annotations;

	/**
	 * @ORM\ManyToMany(targetEntity="Organism")
	 * @ORM\JoinTable(name="ep_entity_organism",
	 *     joinColumns={@ORM\JoinColumn(name="entityId")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="organismId")}
	 * )
	 * @var ArrayCollection
	 */
	protected $organisms;

	public function __construct()
	{
		$this->internalType = 'entity';
		$this->classifications = new ArrayCollection;
		$this->annotations = new ArrayCollection;
		$this->organisms = new ArrayCollection;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getType(): string
	{
		return self::$classToType[get_class($this)];
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Entity
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
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
	 * Set code
	 *
	 * @param string $code
	 *
	 * @return Entity
	 */
	public function setCode($code)
	{
		$this->code = $code;

		return $this;
	}

	/**
	 * Get code
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Set status
	 *
	 * @param EntityStatus $status
	 *
	 * @return Entity
	 */
	public function setStatus(EntityStatus $status)
	{
		$this->status = $status->toInt();

		return $this;
	}

	public function getStatus(): EntityStatus
	{
		if ($this->status === null)
			return EntityStatus::get(EntityStatus::ACTIVE);

		return EntityStatus::fromInt($this->status);
	}

	/**
	 * @return string
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription(string $description): void
	{
		$this->description = $description;
	}

	/**
	 * Add classification
	 *
	 * @param \App\Entity\EntityClassification $classification
	 *
	 * @return Entity
	 */
	public function addClassification(\App\Entity\EntityClassification $classification)
	{
		$this->classifications[] = $classification;

		return $this;
	}

	/**
	 * Remove classification
	 *
	 * @param \App\Entity\EntityClassification $classification
	 */
	public function removeClassification(\App\Entity\EntityClassification $classification)
	{
		$this->classifications->removeElement($classification);
	}

	/**
	 * Get classifications
	 *
	 * @return EntityClassification[]|Collection
	 */
	public function getClassifications()
	{
		return $this->classifications;
	}

	/**
	 * Add annotation
	 *
	 * @param \App\Entity\EntityAnnotation $annotation
	 *
	 * @return Entity
	 */
	public function addAnnotation(\App\Entity\EntityAnnotation $annotation)
	{
		$this->annotations[] = $annotation;
		$annotation->setEntity($this);

		return $this;
	}

	/**
	 * Remove annotation
	 *
	 * @param \App\Entity\EntityAnnotation $annotation
	 */
	public function removeAnnotation(\App\Entity\EntityAnnotation $annotation)
	{
		$this->annotations->removeElement($annotation);
	}

	/**
	 * Get annotations
	 *
	 * @return EntityAnnotation[]|Collection
	 */
	public function getAnnotations()
	{
		return $this->annotations;
	}

	/**
	 * Add organism
	 *
	 * @param \App\Entity\Organism $organism
	 *
	 * @return Entity
	 */
	public function addOrganism(\App\Entity\Organism $organism)
	{
		$this->organisms[] = $organism;

		return $this;
	}

	/**
	 * Remove organism
	 *
	 * @param \App\Entity\Organism $organism
	 */
	public function removeOrganism(\App\Entity\Organism $organism)
	{
		$this->organisms->removeElement($organism);
	}

	/**
	 * Get organisms
	 *
	 * @return Organism[]|Collection
	 */
	public function getOrganisms()
	{
		return $this->organisms;
	}
}

/**
 * @ORM\Entity
 */
class Compartment extends Entity
{
	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Entity")
	 * @ORM\JoinTable(name="ep_entity_location",
	 *     joinColumns={@ORM\JoinColumn(name="childEntityId")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="parentEntityId")}
	 * )
	 */
	protected $parents;

	public function __construct()
	{
		parent::__construct();
		$this->parents = new ArrayCollection;
	}

	/**
	 * @return Compartment[]|ArrayCollection
	 */
	public function getParents()
	{
		return $this->parents;
	}

	public function setParents(array $data)
	{
		self::changeCollection($this->parents, $data, [$this, 'addParent']);
	}

	public function addParent(Compartment $parent)
	{
		$this->parents->add($parent);
	}

	public function removeParent(Entity $parent)
	{
		$this->parents->removeElement($parent);
	}
}

/**
 * @ORM\Entity
 */
class Complex extends Entity
{
	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Compartment")
	 * @ORM\JoinTable(name="ep_entity_location",
	 *     joinColumns={@ORM\JoinColumn(name="childEntityId")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="parentEntityId")}
	 * )
	 */
	protected $compartments;

	public function __construct()
	{
		parent::__construct();
		$this->compartments = new ArrayCollection;
	}

	/**
	 * @return Compartment[]|ArrayCollection
	 */
	public function getCompartments()
	{
		return $this->compartments;
	}

	public function setCompartments(array $data)
	{
		self::changeCollection($this->compartments, $data, [$this, 'addCompartment']);
	}

	public function addCompartment(Compartment $compartment)
	{
		$this->compartments->add($compartment);
		return $this;
	}

	public function removeCompartment(Entity $compartment)
	{
		$this->compartments->removeElement($compartment);
		return $this;
	}
}

/**
 * @ORM\Entity
 */
class Structure extends Entity
{
	/**
	 * @ORM\ManyToMany(targetEntity="Entity")
	 * @ORM\JoinTable(name="ep_entity_composition",
	 *     joinColumns={@ORM\JoinColumn(name="childEntityId")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="parentEntityId")}
	 * )
	 */
	protected $parents;

	public function __construct()
	{
		parent::__construct();
		$this->parents = new ArrayCollection;
	}

	/**
	 * @return Entity[]|ArrayCollection
	 */
	public function getParents()
	{
		return $this->parents;
	}

	public function setParents(array $data)
	{
		self::changeCollection($this->parents, $data, [$this, 'addParent']);
	}

	public function addParent(Complex $entity)
	{
		$this->parents->add($entity);
		return $this;
	}

	public function removeParent(Entity $entity)
	{
		$this->parents->removeElement($entity);
		return $this;
	}
}

/**
 * @ORM\Entity
 */
class Atomic extends Entity
{
	/**
	 * @ORM\ManyToMany(targetEntity="Entity")
	 * @ORM\JoinTable(name="ep_entity_composition",
	 *     joinColumns={@ORM\JoinColumn(name="childEntityId")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="parentEntityId")}
	 * )
	 */
	protected $parents;

	public function __construct()
	{
		parent::__construct();
		$this->parents = new ArrayCollection;
	}

	/**
	 * @return Entity[]|ArrayCollection
	 */
	public function getParents()
	{
		return $this->parents;
	}

	public function setParents(array $data)
	{
		self::changeCollection($this->parents, $data, [$this, 'addParent']);
	}

	public function addParent(Entity $entity)
	{
		if (!($entity instanceof Complex) && !($entity instanceof Structure))
			throw new \TypeError('Atomic parent must be Complex or Structure');

		$this->parents->add($entity);
		return $this;
	}

	public function removeParent(Entity $entity)
	{
		$this->parents->removeElement($entity);
		return $this;
	}
}

/**
 * @ORM\Entity
 */
class AtomicState extends Entity
{
	/**
	 * @var int
	 * @ORM\Column(type="integer", name="parentId")
	 */
	protected $parent;

	public function __construct()
	{
		parent::__construct();
		$this->internalType = 'state';
	}
}

class EntityListener
{
	/**
	 * @param Structure|Atomic|Entity $entity
	 * @throws EntityException
	 */
	private function checkParentComplex(Entity $entity)
	{
		if ($entity->getParents()->filter(function(Entity $parent) { return $parent instanceof Complex; })->isEmpty())
			throw new EntityException('Structure/atomic entity must have at least 1 parent complex');
	}

	/**
	 * @param Complex|Entity $entity
	 * @throws EntityException
	 */
	private function checkCompartment(Entity $entity)
	{
		if ($entity->getCompartments()->isEmpty())
			throw new EntityException('Complex entity must have at least 1 compartment');
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 * @param Entity             $entity
	 * @param PreUpdateEventArgs $args
	 * @throws EntityException
	 */
	public function onUpdate(Entity $entity, PreUpdateEventArgs $args)
	{
		if (!$entity->getStatus()->equalsValue(EntityStatus::ACTIVE))
			return;

		switch (get_class($entity))
		{
			case Compartment::class:
				break;
			case Complex::class:
				$this->checkCompartment($entity);
				break;
			case Structure::class:
				$this->checkParentComplex($entity);
				break;
			case Atomic::class:
				$this->checkParentComplex($entity);
				break;
		}
	}
}

class EntityRepository extends \Doctrine\ORM\EntityRepository
{
	public function findAll()
	{
		return $this->matching(Criteria::create()->where(Criteria::expr()->neq('internalType', 'state')));
	}

	/**
	 * @param Compartment $entity
	 * @return Complex[]|ArrayCollection|\Doctrine\ORM\QueryBuilder
	 */
	public function findComplexChildren(Compartment $entity)
	{
		return new ArrayCollection($this->_em
			->createQuery('SELECT c FROM \\App\\Entity\\Complex c INNER JOIN c.compartments cm WHERE cm.id = :id')
			->setParameters(['id' => $entity->getId()])
			->getResult());
	}

	/**
	 * @param Atomic $entity
	 * @return AtomicState[]|ArrayCollection|\Doctrine\ORM\QueryBuilder
	 */
	public function findAtomicStates(Atomic $entity)
	{
		return new ArrayCollection($this->_em
			->createQuery('SELECT s FROM \\App\\Entity\\AtomicState s WHERE s.parent = :id')
			->setParameters(['id' => $entity->getId()])
			->getResult());
	}
}
