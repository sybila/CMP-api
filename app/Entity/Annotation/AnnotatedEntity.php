<?php
namespace App\Entity;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityNotFoundException;

/**
 * Class AnnotatedEntity
 * Contains information for Doctrine Custom Mapping Type class AnnotableObjectType
 * @package App\Entity
 * @author Radoslav Doktor 433286@mail.muni.cz
 */
class AnnotatedEntity
{
    /**
     * REGISTERED TYPES THAT HAVE ANNOTATIONS
     * APPEND ANOTHER ENTITY THAT SHOULD BE ANNOTABLE (AT THE END)
     */
    const TYPES = [
        1 => Model::class,
        2 => ModelCompartment::class,
        3 => ModelConstraint::class,
        4 => ModelEventAssignment::class,
        5 => ModelSpecie::class,
        6 => ModelReaction::class,
        7 => ModelFunctionDefinition::class,
        8 => ModelReactionItem::class,
        9 => ModelEvent::class,
        10 => ModelInitialAssignment::class,
        11 => ModelParameter::class,
        12 => ModelRule::class,
        13 => Experiment::class,
    ];

    /**
     * ANNOTABLE TYPES, their SLIM route {obj} => ENTITY relation
     * @var string[]
     */
    public static $routeObjToEntityClass = [
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
        'parameter' => ModelParameter::class,
        'rule' => ModelRule::class,
        'experiment' => Experiment::class,
    ];

    /**
     * @param $id
     * @return string
     * @throws EntityNotFoundException
     */
    public static function toType($id): string
    {
        $type = self::TYPES[$id];
        if (is_null($type)){
            throw new EntityNotFoundException(
                "This id does not correspond to any registered annotable type.");
        }
        return $type;
    }

    /**
     * @param string $entity
     * @return int
     * @throws EntityNotFoundException
     */
    public static function fromType(string $entity): int
    {
        foreach (self::TYPES as $id => $controllerType){
            if ($entity===$controllerType){
                return $id;
            }
        }
        dump($entity);exit();
        throw new EntityNotFoundException("This object is not registered annotable type.");
    }

}
class AnnotableObjectType extends Type
{

    const NAME = 'annotable_obj_type';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    /**
     * @inheritDoc
     * @throws EntityNotFoundException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): string
    {
       return AnnotatedEntity::toType($value);
    }

    /**
     * @inheritDoc
     * @throws EntityNotFoundException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): int
    {
        return AnnotatedEntity::fromType($value);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     *
     * @param AbstractPlatform $platform
     *
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        return [Types::INTEGER];
    }

}