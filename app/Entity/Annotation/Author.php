<?php
namespace App\Entity;

use App\Entity\IdentifiedObject;
use App\Entity\Identifier;


/**
 * Class Author
 * @ORM\Entity
 * @ORM\Table(name="author")
 */
class Author implements IdentifiedObject
{
    use Identifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $surname;

    /**
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\Column(name="user_id")
     */
    protected $userId;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     */
    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }


}