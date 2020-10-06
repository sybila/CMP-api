<?php

namespace App\Controllers;

use App\Entity\{IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\PhysicalQuantityRepository,
    Repositories\UnitAliasRepository,
    Repositories\UnitRepository,
    Unit,
    UnitAlias};
use App\Exceptions\{MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;
use UnitEndpointAuthorizable;

/**
 * @property-read UnitAliasRepository $repository
 * @method UnitAlias getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class UnitAliasController extends ParentedRepositoryController
{



    use UnitEndpointAuthorizable;

	/** @var UnitAliasRepository */
	private $unitAliasRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->unitAliasRepository = $v->get(UnitAliasRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $unitAlias): array
	{
		/** @var UnitAlias $unitAlias */
		return [
		    'id' => $unitAlias->getId(),
			'alternative_name' => $unitAlias->getAlternativeName(),
		];
	}

	protected function setData(IdentifiedObject $unit, ArgumentParser $data): void
	{
		/** @var UnitAlias $unitAlias */
		$unitAlias->getUnitId() ?: $unitAlias->setUnitId($this->repository->getParent());
		!$data->hasKey('alternative_name') ?: $unitAlias->setAlternativeName($data->getString('alternative_name'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('alternative_name'))
			throw new MissingRequiredKeyException('alternative_name');
		return new UnitAlias;
	}

	protected function checkInsertObject(IdentifiedObject $unitAlias): void
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
		return UnitAliasRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return UnitRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['unit-id', 'unit'];
	}

    protected static function getAlias(): string
    {
        return 'a';
    }

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        // TODO: Implement checkParentValidity() method.
    }
}
