<?php

namespace App\Entity;


use App\Exceptions\EntityClassificationException;
use App\Exceptions\EntityHierarchyException;
use App\Exceptions\EntityLocationException;
use App\Helpers\
{
	ChangeCollection, ConsistenceEnum
};
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\Tests\StringClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="model_unit_definition")
 * @ORM\DiscriminatorColumn(name="hierarchy_type", type="string")
 */
class ModelUnitDefinition implements IdentifiedObject
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer|null
	 */
	private $id;


	/**
	 * @ORM\ManyToOne(targetEntity="Model", inversedBy="compartments")
	 * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
	 */
	protected $modelId;

	/**
	 * @ORM\ManyToMany(targetEntity="ModelUnit")
	 * @ORM\JoinTable(name="model_unit_to_definition",
	 *     joinColumns={@ORM\JoinColumn(name="model_unit_definition_id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="model_unit_id")}
	 * )
	 * @var ArrayCollection
	 */
	protected $units;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Get modelId
	 *
	 * @return integer|null
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * Set modelId
	 *
	 * @param integer $modelId
	 *
	 * @return ModelUnitToDefinition
	 */
	public function setModelId($modelId): ModelUnitToDefinition
	{
		$this->modelId = $modelId;
		return $this;
	}

	/**
	 * Get name
	 *
	 * @return null|string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}


	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return ModelUnitToDefinition
	 */
	public function setName($name): ModelUnitToDefinition
	{
		$this->name = $name;
		return $this;
	}


}
