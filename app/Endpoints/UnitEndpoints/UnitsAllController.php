<?php

namespace App\Controllers;

use App\Entity\{Attribute,
    Authorization\User,
    Bioquantity,
    Experiment,
    IdentifiedObject,
    ExperimentVariable,
    ExperimentNote,
    Device,
    Model,
    PhysicalQuantity,
    Repositories\BioquantityRepository,
    Repositories\DeviceRepository,
    Repositories\ExperimentVariableRepository,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ModelRepository,
    Repositories\OrganismRepository,
    Repositories\PhysicalQuantityRepository,
    Repositories\UnitRepository,
    Repositories\UnitsAllRepository,
    Unit,
    UnitAlias};
use App\Exceptions\{
	DependentResourcesBoundException,
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read Repository $repository
 * @method Unit getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class UnitsAllController extends WritableRepositoryController
{
	/** @var UnitsAllRepository */
	private $unitsAllRepository;

    public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->unitsAllRepository = $c->get(UnitsAllRepository::class);
	}

    protected static function getAlias(): string
    {
        return 'u';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'preferred_name'];
	}

    protected function getData(IdentifiedObject $unit): array
    {
        /** @var Unit $unit */
        return [
            'id' => $unit->getId(),
            'preferred_name' => $unit->getPreferredName(),
            'alternative_names' => $unit->getAliases()->map(function (UnitAlias $alias) {
                return ['alias' => $alias->getAlternativeName()];
            })->toArray(),
            'coefficient' => $unit->getCoefficient(),
            'sbml_id' => $unit->getSbmlId()
        ];
    }

    protected function setData(IdentifiedObject $unit, ArgumentParser $data): void
    {
        /** @var Unit $unit */
        !$data->hasKey('preferred_name') ?: $unit->setPreferredName($data->getString('preferred_name'));
        !$data->hasKey('coefficient') ?: $unit->setCoefficient($data->getFloat('coefficient'));
        !$data->hasKey('sbml_id') ?: $unit->setSbmlId($data->getString('sbml_id'));
    }

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
        if (!$body->hasKey('preferred_name'))
            throw new MissingRequiredKeyException('preferred_name');
        if (!$body->hasKey('coefficient'))
            throw new MissingRequiredKeyException('coefficient');
        return new Unit;
	}

	protected function checkInsertObject(IdentifiedObject $unit): void
	{
        /** @var Unit $unit */
        if ($unit->getQuantityId() === null)
            throw new MissingRequiredKeyException('quantityId');
        if ($unit->getPreferredName() === null)
            throw new MissingRequiredKeyException('preferred_name');
        if ($unit->getCoefficient() === null)
            throw new MissingRequiredKeyException('coefficient');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
        /** @var Unit $unit */
        $unit = $this->getObject($args->getInt('id'));
        if (!$unit->getAliases()->isEmpty())
            $unit->getAliases()->clear();
        return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
        return new Assert\Collection( [
            'quantityId' => new Assert\Type(['type' => 'integer']),
        ]);
	}

	protected static function getObjectName(): string
	{
        return 'unit';
	}

	protected static function getRepositoryClassName(): string
	{
        return UnitsAllRepository::Class;
	}
}
