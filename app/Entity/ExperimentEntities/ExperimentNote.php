<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_note")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ExperimentNote implements IdentifiedObject
{
	use EBase;

	/**
	 * @ORM\ManyToOne(targetEntity="Experiment", inversedBy="note")
	 * @ORM\JoinColumn(name="exp_id", referencedColumnName="id")
	 */
	protected $experimentId;

	/**
	 * @ORM\ManyToOne(targetEntity="ExperimentVariable", inversedBy="note")
	 * @ORM\JoinColumn(name="var_id", referencedColumnName="id")
	 */
	protected $variableId;

	/**
	 * @var float
	 * @ORM\Column(type="float", name="time")
	 */
	private $time;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="note")
	 */
	private $note;

	/**
	 * @var string
	 * @ORM\Column(type="string", name="img_link")
	 */
	private $imgLink;

	
	/**
	 * Get id
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get experimentId
	 * @return integer
	 */
	public function getExperimentId()
	{
		return $this->experimentId;
	}

	/**
	 * Set experimentId
	 * @param integer $experimentId
	 * @return ExperimentNote
	 */
	public function setExperimentId($experimentId): ExperimentNote
	{
		$this->experimentId = $experimentId;
		return $this;
	}

	/**
	 * Get variableId
	 * @return integer|null
	 */
	public function getVariableId()
	{
		return $this->variableId;
	}

	/**
	 * Set variableId
	 * @param integer $variableId
	 * @return ExperimentNote
	 */
	public function setVariableId($variableId): ExperimentNote
	{
		$this->variableId = $variableId;
		return $this;
	}

	/**
	 * Get time
	 * @return null|float
	 */
	public function getTime(): ?float
	{
		return $this->time;
	}

	/**
	 * Set time
	 * @param float $time
	 * @return ExperimentNote
	 */
	public function setTime($time): ExperimentNote
	{
		$this->time = $time;
		return $this;
	}

	/**
	 * Get note
	 * @return string|null
	 */
	public function getNote(): ?string
	{
		return $this->note;
	}

	/**
	 * Set note
	 * @param string $note
	 * @return ExperimentNote
	 */
	public function setNote($note): ExperimentNote
	{
		$this->note = $note;
		return $this;
	}

	/**
	 * Get imgLink
	 * @return string|null
	 */
	public function getimgLink(): ?string
	{
		return $this->imgLink;
	}

	/**
	 * Set imgLink
	 * @param string $imgLink
	 * @return ExperimentNote
	 */
	public function setImgLink($imgLink): ExperimentNote
	{
		$this->imgLink = $imgLink;
		return $this;
	}
}