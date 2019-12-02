<?php

namespace App\Entity;

use App\Helpers\DateTimeJson;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="protocol")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Protocol implements IdentifiedObject
{
	use EBase;

	/**
	 * @var DateTimeJson
	 * @ORM\Column(type="datetime", name="inserted")
	 */
	private $inserted;

	/**
	 * @var protocol
	 * @ORM\Column(type="string", name="protocol")
	 */
	private $protocol;

    /**
     * Many Protocols have Many Experiments.
     * @ORM\ManyToMany(targetEntity="Experiment", inversedBy="protocols")
     * @ORM\JoinTable(name="experiment_to_protocol",  joinColumns={@ORM\JoinColumn(name="protocol_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="exp_id", referencedColumnName="id")})
     */
    private $experiments;

    public function __construct()
    {
        $this->inserted = new DateTimeJson;
    }

	/**
	 * Get inserted
	 * @return DateTimeJson|null
	 */
	public function getInserted(): ?DateTimeJson
	{
		return $this->inserted;
	}

    /**
     * Get protocol
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

	/**
	 * Set protocol
	 * @param string $protocol
	 * @return Protocol
	 */
	public function setProtocol($protocol): Protocol
	{
		$this->protocol = $protocol;
		return $this;
	}

	/**
	 * @return Experiment[]|Collection
	 */
	public function getExperiments(): Collection
	{
		return $this->experiments;
	}

    /**
     * @param Experiment $experiment
     */
    public function addExperiment(Experiment $experiment)
    {
        if ($this->experiments->contains($experiment)) {
            return;
        }
        $this->experiments->add($experiment);
        $experiment->addProtocol($this);
    }

    /**
     * @param Experiment $experiment
     */
    public function removeExperiment(Experiment $experiment)
    {
        if (!$this->experiments->contains($experiment)) {
            return;
        }
        $this->experiments->removeElement($experiment);
        $experiment->removeProtocol($this);
    }

}
