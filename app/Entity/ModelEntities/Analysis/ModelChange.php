<?php


use App\Entity\IdentifiedObject;
use App\Entity\ModelInitialAssignment;
use App\Entity\ModelParameter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

class ModelChange implements IdentifiedObject
{
    use \App\Entity\SBase;

    /**
     * @var int
     * @ORM\Column(type="integer", name="user_id")
     */
    private $userId;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ModelInitialAssignment", mappedBy="modelId")
     */
    private $initialAssignments;

	/**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ModelParameter", mappedBy="modelId")
     */
	private $parameters;

    /**
     * Get userId
     * @return integer
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Set userId
     * @param int $userId
     * @return ModelChange
     */
    public function setUserId($userId): ModelChange
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return ModelInitialAssignment[]|Collection
     */
    public function getInitialAssignments(): Collection
    {
        return $this->initialAssignments;
    }

    /**
     * @return ModelParameter[]|Collection
     */
    public function getParameters(): Collection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('reactionId', null));
        return $this->parameters->matching($criteria);
    }

}