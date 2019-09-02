<?php


namespace App\Entity;

use App\Entity\Identifier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class Group
{
	private $id;

	private $name;

	private $type;
}
