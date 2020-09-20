<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="analysis_tool")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class AnalysisTool implements IdentifiedObject
{
    use AnalysisBase;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $location;

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

}