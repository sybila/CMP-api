<?php

namespace App\Entity;

use App\Exceptions\RuleClassificationException;
use App\Helpers\ChangeCollection;
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
	use ChangeCollection;
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
		$this->status = RuleStatus::get(RuleStatus::PENDING)->toInt();
		//FIXME
		$this->isValid = false;
	}

	public function getCode(): ?string
	{
		return $this->code;
	}

	public function setCode(string $code): void
	{
		$this->code = $code;
	}

	public function getModifier(): ?string
	{
		return $this->modifier;
	}

	public function setModifier(string $modifier): void
	{
		$this->modifier = $modifier;
	}

	public function getEquation(): ?string
	{
		return $this->equation;
	}

	public function setEquation(string $equation): void
	{
		$this->equation = $equation;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setStatus(RuleStatus $status): void
	{
		$this->status = $status->toInt();
	}

	public function getStatus(): RuleStatus
	{
		if ($this->status === null)
			return RuleStatus::get(RuleStatus::ACTIVE);

		return RuleStatus::fromInt($this->status);
	}

	public function addClassification(Classification $classification)
	{
		if (!($classification instanceof RuleClassification))
			throw new RuleClassificationException;

		$this->classifications[] = $classification;
	}

	public function removeClassification(RuleClassification $classification)
	{
		$this->classifications->removeElement($classification);
	}

	/**
	 * @return RuleClassification[]|Collection
	 */
	public function getClassifications(): Collection
	{
		return $this->classifications;
	}

	public function setClassifications(array $data): void
	{
		self::changeCollection($this->classifications, $data, [$this, 'addClassification']);
	}

	/**
	 * @param RuleAnnotation|Annotation $annotation
	 */
	public function addAnnotation(Annotation $annotation): void
	{
		$this->annotations[] = $annotation;
		$annotation->setRule($this);
	}

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

	public function addOrganism(Organism $organism): void
	{
		$this->organisms[] = $organism;
	}

	public function removeOrganism(Organism $organism): void
	{
		$this->organisms->removeElement($organism);
	}

	/**
	 * @return Organism[]|Collection
	 */
	public function getOrganisms(): Collection
	{
		return $this->organisms;
	}

	public function setOrganisms(array $data): void
	{
		self::changeCollection($this->organisms, $data);
	}

	/**
	 * @return RuleNote[]|Collection
	 */
	public function getNotes(): Collection
	{
		return $this->notes;
	}

	/**
	 * @param RuleNote|BcsNote $note
	 */
	public function addNote(BcsNote $note): void
	{
		$this->notes->add($note);
	}

	/**
	 * @param RuleNote|BcsNote $note
	 */
	public function removeNote(BcsNote $note): void
	{
		$this->notes->removeElement($note);
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(string $description): void
	{
		$this->description = $description;
	}
}
