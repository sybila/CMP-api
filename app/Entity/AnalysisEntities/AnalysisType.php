<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="analysis_type")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class AnalysisType implements IdentifiedObject
{
    use AnalysisBase;

}