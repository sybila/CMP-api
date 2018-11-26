<?php

namespace App\Entity;


use App\Exceptions\EntityClassificationException;
use App\Exceptions\EntityHierarchyException;
use App\Exceptions\EntityLocationException;
use App\Helpers\
{
	ChangeCollection, ConsistenceEnum
};
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\Tests\StringClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_event_assignment")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelEventAssignment implements IdentifiedObject
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;


	/**
	 * @ORM\ManyToOne(targetEntity="ModelEvent", inversedBy="eventAssignments")
	 * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
	 */
	protected $eventId;


	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $formula;


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
	 * Get eventId
	 *
	 * @return integer|null
	 */
	public function getEventId()
	{
		return $this->eventId;
	}

	/**
	 * Set eventId
	 *
	 * @param integer $eventId
	 *
	 * @return ModelEventAssignment
	 */
	public function setEventId($eventId): ModelEventAssignment
	{
		$this->eventId = $eventId;
		return $this;
	}

	/**
	 * Get formula
	 *
	 * @return null|string
	 */
	public function getFormula(): ?string
	{
		return $this->formula;
	}


	/**
	 * Set formula
	 *
	 * @param string $formula
	 *
	 * @return ModelEventAssignment
	 */
	public function setFormula($formula): ModelEventAssignment
	{
		$this->formula = $formula;
		return $this;
	}

}
