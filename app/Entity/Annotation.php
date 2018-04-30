<?php

namespace App\Entity;

use App\Helpers\ConsistenceEnum;
use Doctrine\Common\Collections\Collection;
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
	const NCBI = 'ncbi';
	const CBS = 'cbs';

	public static $names = [
		self::BIONUMBERS => 'Bionumbers',
		self::CHEBI => 'Chebi',
		self::DOI => 'Digital Object Identifier',
		self::EC => 'GenomeNet: Enzyme',
		self::GO => 'Gene Ontology Consortium',
		self::KEGG => 'Kegg',
		self::PUBCHEM => 'PubChem',
		self::UNIPROT => 'Uniprot',
		self::URL => 'URL Address',
		self::NCBI => 'National Center for Biotechnology Information',
		self::CBS => 'Cyanobase',
	];

	public function getLink($id)
	{
		switch ($this->getValue())
		{
			case self::CHEBI:
				return 'http://www.ebi.ac.uk/chebi/chebiOntology.do?chebiId=CHEBI:' . $id;
			case self::GO:
				return 'http://amigo.geneontology.org/cgi-bin/amigo/term_details?term=GO:' . $id;
			case self::KEGG:
				return 'http://www.genome.jp/dbget-bin/www_bget?' . $id;
			case self::EC:
				return 'http://www.genome.jp/dbget-bin/www_bget?ec:' . $id;
			case self::CBS:
				return 'http://genome.microbedb.jp/cyanobase/genes/search?q=' . $id . '&kb=search&m=gene_symbol%2Cgi_gname%2Cdefinition%2Cgi_pname';
			case self::UNIPROT:
				return 'http://www.uniprot.org/uniprot/' . $id;
			case self::PUBCHEM:
				return 'https://pubchem.ncbi.nlm.nih.gov/summary/summary.cgi?cid=' . $id;
			case self::DOI:
				return 'http://dx.doi.org/' . $id;
			case self::BIONUMBERS:
				return 'http://bionumbers.hms.harvard.edu/bionumber.aspx?id=' . $id;
			case self::NCBI:
				return 'https://www.ncbi.nlm.nih.gov/search/?term=' . $id;
			case self::URL:
			default:
				return $id;
		}
	}
}

interface IAnnotatedObject
{
	public function addAnnotation(Annotation $annotation): void;
	public function removeAnnotation(Annotation $annotation): void;

	/**
	 * @return Annotation[]|Collection
	 */
	public function getAnnotations(): Collection;
}

/**
 * @ORM\Entity
 * @ORM\Table(name="ep_annotation")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="itemType", type="string")
 * @ORM\DiscriminatorMap({"entity" = "EntityAnnotation", "reaction" = "RuleAnnotation"})
 */
abstract class Annotation implements IdentifiedObject
{
	use Identifier;

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
