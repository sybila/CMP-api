<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ep_classification")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"entity" = "EntityClassification", "reaction" = "RuleClassification"})
 */
abstract class Classification implements IdentifiedObject
{
	use Identifier;

	public static $classToType = [
		EntityClassification::class => 'entity',
		RuleClassification::class => 'rule',
	];

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @return string
	 */
	public function getName(): ?string
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
}

/**
 * @ORM\Entity
 */
class EntityClassification extends Classification
{
}

/**
 * @ORM\Entity
 */
class RuleClassification extends Classification
{
}
