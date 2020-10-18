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
    Repositories\UnitsAliasesAllRepository,
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
 * @method UnitAlias getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class UnitsAliasesAllController extends WritableRepositoryController
{
	/** @var UnitsAliasesAllRepository */
	private $unitsAliasesAllRepository;

    public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->unitsAliasesAllRepository = $c->get(UnitsAliasesAllRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'alternative_name'];
	}

    protected function getData(IdentifiedObject $unitAlias): array
    {
        /** @var UnitAlias $unitAlias */
        return [
            'id' => $unitAlias->getId(),
            'unit_id' => $unitAlias->getUnitId(),
            'alternative_name' => $unitAlias->getAlternativeName(),
        ];
    }

    protected function setData(IdentifiedObject $unitAlias, ArgumentParser $data): void
    {
        /** @var UnitAlias $unitAlias */
        $unitAlias->getUnitId() ?: $unitAlias->setUnitId($this->repository->getParent());
        !$data->hasKey('alternative_name') ?: $unitAlias->setAlternativeName($data->getString('alternative_name'));
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
        /** @var UnitAlias $unitAlias */
        if ($unitAlias->getUnitId() === null)
            throw new MissingRequiredKeyException('unitId');
        if ($unitAlias->getAlternativeName() === null)
            throw new MissingRequiredKeyException('alternative_name');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
        return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
        return new Assert\Collection( [
            'unitId' => new Assert\Type(['type' => 'integer']),
        ]);
	}

	protected static function getObjectName(): string
	{
        return 'unitAlias';
	}

	protected static function getRepositoryClassName(): string
	{
        return UnitsAliasesAllRepository::Class;
	}
}
