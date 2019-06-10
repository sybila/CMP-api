<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_task_change")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelChange implements IdentifiedObject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer|null
     */
    private $id;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="ModelTask", inversedBy="modelChanges")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    private $taskId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $origin_id;


    /**
     * @var float
     * @ORM\Column(type="float")
     */
    private $value;


    /**
     * Get id
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * @param int $taskId
     * @return ModelChange
     */
    public function setTaskId(int $taskId): ModelChange
    {
        $this->taskId = $taskId;
        return $this;
    }

    /**
     * @return int
     */
    public function getTaskId(): int
    {
        return $this->taskId;
    }

    /**
     * @param string $type
     * @return ModelChange
     */
    public function setType(string $type): ModelChange
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param int $origin_id
     * @return ModelChange
     */
    public function setOriginId(int $origin_id): ModelChange
    {
        $this->origin_id = $origin_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getOriginId(): int
    {
        return $this->origin_id;
    }

    /**
     * @param int $value
     * @return ModelChange
     */
    public function setValue(int $value): ModelChange
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

}