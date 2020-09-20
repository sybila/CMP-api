<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="analysis_method")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class AnalysisMethod implements IdentifiedObject
{
    use AnalysisBase;

    /**
     * @var array stores the names, type, boundaries and default values in JSON format.
     * @ORM\Column(type="json", name="method_signature")
     */
    private $methodSignature;

    /**
     * @var int
     * @ORM\Column(type="integer", name="type_id")
     */
    private $typeId;

    /**
     * @var int
     * @ORM\Column(type="integer", name="tool_id")
     */
    private $toolId;

    /**
     * @var int
     * @ORM\Column(type="integer", name="vis_id")
     */
    private $visId;

    /**
     * @return array
     */
    public function getMethodSignature(): array
    {
        return $this->methodSignature;
    }

    /**
     * @param string $methodSignature
     */
    public function setMethodSignature(string $methodSignature): void
    {
        $this->methodSignature = $methodSignature;
    }

    /**
     * @return int
     */
    public function getToolId(): int
    {
        return $this->toolId;
    }

    /**
     * @param int $toolId
     */
    public function setToolId(int $toolId): void
    {
        $this->toolId = $toolId;
    }

    /**
     * @return int
     */
    public function getVisId(): int
    {
        return $this->visId;
    }

    /**
     * @param int $visId
     */
    public function setVisId(int $visId): void
    {
        $this->visId = $visId;
    }

    /**
     * @return int
     */
    public function getTypeId(): int
    {
        return $this->typeId;
    }

    /**
     * @param int $typeId
     */
    public function setTypeId(int $typeId): void
    {
        $this->typeId = $typeId;
    }



}