<?php

namespace App\Entity;

use App\Helpers\ConsistenceEnum;
use Consistence\Enum\InvalidEnumValueException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


final class RuleStatus extends ConsistenceEnum
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

	public static function fromInt(int $value): RuleStatus
	{
		$key = array_search($value, self::$toInt, true);
		if ($key === false)
			throw new InvalidEnumValueException($value, array_values(self::$toInt));

		return self::get($key);
	}
}

/**
 * @ORM\Entity
 * @ORM\Table(name="ep_reaction")
 */
class Rule implements IdentifiedObject, IAnnotatedObject, IBcsNoteObject
{
	use Identifier;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $code;

	/**
	 * @var string
	 * @ORM\Column(name="modifier",type="string")
	 */
	protected $modifier;

	/**
	 * @var string
	 * @ORM\Column(name="equation",type="string")
	 */
	protected $equation;

	/**
	 * @var string
	 * @ORM\Column(name="description",type="string")
	 */
	protected $description;

	/**
	 * @var int
	 * @ORM\Column(name="active",type="integer")
	 */
	protected $status;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $isValid;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="RuleClassification")
	 * @ORM\JoinTable(name="ep_reaction_classification",
	 *     joinColumns={@ORM\JoinColumn(name="reactionId")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="classificationId")}
	 * )
	 */
	protected $classifications;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="RuleAnnotation", mappedBy="rule", cascade={"persist", "remove"})
	 */
	protected $annotations;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Organism")
	 * @ORM\JoinTable(name="ep_reaction_organism",
	 *     joinColumns={@ORM\JoinColumn(name="reactionId")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="organismId")}
	 * )
	 */
	protected $organisms;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="EntityNote", mappedBy="entity", cascade={"persist", "remove"})
	 */
	protected $notes;

	public function __construct()
	{
		$this->classifications = new ArrayCollection;
		$this->annotations = new ArrayCollection;
		$this->organisms = new ArrayCollection;
		$this->notes = new ArrayCollection;
	}

	/**
	 * @return mixed
	 */
	public function getCode(): ?string
	{
		return $this->code;
	}

	/**
	 * @param mixed $code
	 */
	public function setCode(string $code): void
	{
		$this->code = $code;
	}

	/**
	 * @return mixed
	 */
	public function getModifier(): ?string
	{
		return $this->modifier;
	}

	/**
	 * @param mixed $modifier
	 */
	public function setModifier(string $modifier): void
	{
		$this->modifier = $modifier;
	}

	/**
	 * @return mixed
	 */
	public function getEquation(): string
	{
		return $this->equation;
	}

	/**
	 * @param mixed $equation
	 */
	public function setEquation(string $equation): void
	{
		$this->equation = $equation;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Rule
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
	 * Set status
	 *
	 * @param RuleStatus $status
	 *
	 * @return Rule
	 */
	public function setStatus(RuleStatus $status)
	{
		$this->status = $status->toInt();

		return $this;
	}

	/**
	 * Get status
	 *
	 * @return RuleStatus
	 */
	public function getStatus()
	{
		return RuleStatus::fromInt($this->status);
	}

	/**
	 * Add classification
	 *
	 * @param RuleClassification $classification
	 *
	 * @return Rule
	 */
	public function addClassification(RuleClassification $classification)
	{
		$this->classifications[] = $classification;

		return $this;
	}

	/**
	 * Remove classification
	 *
	 * @param RuleClassification $classification
	 */
	public function removeClassification(RuleClassification $classification)
	{
		$this->classifications->removeElement($classification);
	}

	/**
	 * Get classifications
	 *
	 * @return RuleClassification[]|Collection
	 */
	public function getClassifications()
	{
		return $this->classifications;
	}

	/**
	 * @param RuleAnnotation $annotation
	 */
	public function addAnnotation(Annotation $annotation): void
	{
		$this->annotations[] = $annotation;
		$annotation->setRule($this);
	}

	/**
	 * @param RuleAnnotation $annotation
	 */
	public function removeAnnotation(Annotation $annotation): void
	{
		$this->annotations->removeElement($annotation);
	}

	/**
	 * @return RuleAnnotation[]|Collection
	 */
	public function getAnnotations(): Collection
	{
		return $this->annotations;
	}

	/**
	 * Add organism
	 *
	 * @param Organism $organism
	 *
	 * @return Rule
	 */
	public function addOrganism(Organism $organism)
	{
		$this->organisms[] = $organism;

		return $this;
	}

	/**
	 * Remove organism
	 *
	 * @param Organism $organism
	 */
	public function removeOrganism(Organism $organism)
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

	/**
	 * @return ArrayCollection
	 */
	public function getNotes(): Collection
	{
		return $this->notes;
	}

	public function addNote(BcsNote $note): void
	{
		$this->notes->add($note);
	}

	public function removeNote(BcsNote $note): void
	{
		$this->notes->removeElement($note);
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function setDescription(string $description): void
	{
		$this->description = $description;
	}
}
