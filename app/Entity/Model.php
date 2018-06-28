<?php

namespace App\Entity;


use App\Exceptions\EntityClassificationException;
use App\Exceptions\EntityHierarchyException;
use App\Exceptions\EntityLocationException;
use App\Helpers\
{
	ChangeCollection, ConsistenceEnum
};
use App\Exceptions\EntityException;
use Consistence\Enum\InvalidEnumValueException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\Tests\StringClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="ep_model")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class Model implements IdentifiedObject
{


	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $unitId;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $userId;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $description;

	/**
	 * @var string
	 * @ORM\Column (type="string")
	 */
	protected $status;

	/**
	 * @var string
	 * @ORM\Column (type="string")
	 */
	protected $solver;
	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Model
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	public function getId(): ?int
	{
		// TODO: Implement getId() method.
		return 2;
	}
}
