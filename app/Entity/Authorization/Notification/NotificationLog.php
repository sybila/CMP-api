<?php

namespace App\Entity\Authorization\Notification;

use App\Entity\IdentifiedObject;
use App\Entity\Identifier;
use App\Helpers\DateTimeJson;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * @ORM\Entity
 * @ORM\Table(name="notification_log")
 */
class NotificationLog implements IdentifiedObject
{
    use Identifier;

    /**
     * map to users
     * @ORM\Column
     */
    private $whoId;

    /**
     * @var string
     * @ORM\Column
     */
    private $what;

    /**
     * @var string
     * @ORM\Column(name="which_parent")
     */
    private $whichParent;

    /**
     * @ORM\Column(name="whenDT")
     */
    private $when;

    /**
     * @var string
     * @ORM\Column
     */
    private $how;


    /**
     * @return mixed
     */
    public function getWhoId()
    {
        return $this->whoId;
    }

    /**
     * @param mixed $whoId
     */
    public function setWhoId($whoId): void
    {
        $this->whoId = $whoId;
    }

    /**
     * @return string
     */
    public function getWhat(): string
    {
        return $this->what;
    }

    /**
     * @param string $what
     */
    public function setWhat(string $what): void
    {
        $this->what = $what;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getWhen(): string
    {
        return $this->when;
    }


    public function setWhen(): void
    {
        $this->when = json_encode(new DateTimeJson);
    }

    /**
     * @return string
     */
    public function getHow(): string
    {
        return $this->how;
    }

    /**
     * @param string $how
     */
    public function setHow(string $how): void
    {
        $this->how = $how;
    }

    /**
     * @return string
     */
    public function getWhichParent(): string
    {
        return $this->whichParent;
    }

    /**
     * @param string $whichParent
     */
    public function setWhichParent(string $whichParent): void
    {
        $this->whichParent = $whichParent;
    }

}