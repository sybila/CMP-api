<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AnnotableResourceType
 * @ORM\Entity
 * @ORM\Table(name="annotable_object")
 */
class AnnotableObject implements IdentifiedObject
{
    use Identifier;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer|null
     */
    private $id;

    /**
     * @var string
     * @ORM\Column
     */
    private $type;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    const KNOWN_TYPES = [
        'model' => Model::class,
        'compartment' => ModelCompartment::class,
        'constraint' => ModelConstraint::class,
        'eventAssignment' => ModelEventAssignment::class,
        'specie' => ModelSpecie::class,
        'reaction' => ModelReaction::class,
        'functionDefinition' => ModelFunctionDefinition::class,
        'reactionItem' => ModelReactionItem::class,
        'event' => ModelEvent::class,
        'initialAssignment' => ModelInitialAssignment::class,
        'parameters' => ModelParameter::class,
        'rules' => ModelRule::class,
        ];
}