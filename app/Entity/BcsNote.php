<?php

namespace App\Entity;

use App\Helpers\DateTimeJson;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

interface IBcsNoteObject
{
	public function addNote(BcsNote $note);
	public function removeNote(BcsNote $note);

	/**
	 * @return BcsNote[]|Collection
	 */
	public function getNotes(): Collection;
}

abstract class BcsNote implements IdentifiedObject
{
	/**
	 * @var int
	 * @ORM\Column(type="integer",name="userId",nullable=true)
	 */
	protected $user;

	/**
	 * @var string
	 * @ORM\Column(type="string",nullable=true)
	 */
	protected $text;

	/**
	 * @var DateTimeJson
	 * @ORM\Column(type="datetime")
	 */
	protected $inserted;

	/**
	 * @var DateTimeJson
	 * @ORM\Column(type="datetime")
	 */
	protected $updated;

	public function __construct()
	{
		$this->inserted = new DateTimeJson;
	}

	public function getUser(): int
	{
		return $this->user;
	}

	public function setUser(int $user): void
	{
		$this->user = $user;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function setText(string $text): void
	{
		$this->text = $text;
	}

	public function getInserted(): DateTimeJson
	{
		return $this->inserted;
	}

	public function getUpdated(): DateTimeJson
	{
		return $this->updated;
	}

	public function setUpdated(DateTimeJson $updated): void
	{
		$this->updated = $updated;
	}
}

/**
 * @ORM\Entity
 * @ORM\Table(name="ep_entity_note")
 */
class EntityNote extends BcsNote
{
	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="Entity", inversedBy="notes")
	 * @ORM\JoinColumn(name="entityId", referencedColumnName="id")
	 */
	protected $entity;

	public function setEntity(Entity $entity)
	{
		$this->entity = $entity;
	}
}

/**
 * @ORM\Entity
 * @ORM\Table(name="ep_reaction_note")
 */
class RuleNote extends BcsNote
{
	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="Rule", inversedBy="notes")
	 * @ORM\JoinColumn(name="reactionId", referencedColumnName="id")
	 */
	protected $rule;

	public function setRule(Rule $rule)
	{
		$this->rule = $rule;
	}
}
