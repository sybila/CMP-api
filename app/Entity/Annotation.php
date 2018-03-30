<?php

namespace App\Entity;

use App\Helpers\ConsistenceEnum;
use Doctrine\ORM\Mapping as ORM;

final class AnnotationTerm extends ConsistenceEnum
{
	const BIONUMBERS = 'bnid';
	const CHEBI = 'chebi';
	const DOI = 'doi';
	const EC = 'ec-code';
	const GO = 'go';
	const KEGG = 'kegg';
	const PUBCHEM = 'pubchem';
	const UNIPROT = 'uniprot';
	const URL = 'url';
}

/**
 * @ORM\Entity
 * @ORM\Table(name="ep_annotation")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="itemType", type="string")
 * @ORM\DiscriminatorMap({"entity" = "EntityAnnotation", "reaction" = "RuleAnnotation"})
 */
abstract class Annotation
{
	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(type="string",nullable=true)
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(type="string",nullable=true)
	 */
	protected $description;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $termId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $termType;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $itemId;

	/**
	 * @param AnnotationTerm $value
	 */
	public function setTermType(AnnotationTerm $value)
	{
		$this->termType = $value->getValue();
	}

	/**
	 * @return AnnotationTerm
	 */
	public function getTermType(): string
	{
		return AnnotationTerm::get($this->termType);
	}

	public function getTermId(): string
	{
		return $this->termId;
	}

	public function setTermId(string $value): void
	{
		$this->termId = $value;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
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
	 * @return int
	 */
	public function getItemId(): int
	{
		return $this->itemId;
	}

	/**
	 * @param int $itemId
	 */
	public function setItemId(int $itemId): void
	{
		$this->itemId = $itemId;
	}
}

/**
 * @ORM\Entity
 */
class EntityAnnotation extends Annotation
{
	/**
	 * @ORM\ManyToOne(targetEntity="Entity", inversedBy="annotations")
	 * @ORM\JoinColumn(name="itemId", referencedColumnName="id")
	 */
	protected $entity;

	public function setEntity(Entity $entity)
	{
		$this->entity = $entity;
	}
}

/**
 * @ORM\Entity
 */
class RuleAnnotation extends Annotation
{
	/**
	 * @ORM\ManyToOne(targetEntity="Rule", inversedBy="annotations")
	 * @ORM\JoinColumn(name="itemId", referencedColumnName="id")
	 */
	protected $rule;

	public function setRule(Rule $rule)
	{
		$this->rule = $rule;
	}
}
