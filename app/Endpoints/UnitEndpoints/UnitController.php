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
 * @property-read UnitRepository $repository
 * @method Unit getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class UnitController extends ParentedRepositoryController
{

    use UnitEndpointAuthorizable;

	/** @var UnitRepository */
	private $unitRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->unitRepository = $v->get(UnitRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'preferred_name', 'coefficient', 'sbmlId'];
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
		$unit->getQuantityId() ?: $unit->setQuantityId($this->repository->getParent());
		!$data->hasKey('preferred_name') ?: $unit->setPreferredName($data->getString('preferred_name')) &&
            $this->setNewAlias($unit, $data->getString('preferred_name'));
		!$data->hasKey('coefficient') ?: $unit->setCoefficient($data->getFloat('coefficient'));
		!$data->hasKey('sbml_id') ?: $unit->setSbmlId($data->getString('sbml_id'));
	}

	protected function setNewAlias($unit, $name){
        $newAlias = new UnitAlias();
        $newAlias->setAlternativeName($name);
        $unit->addUnitAlias($newAlias);
        $this->orm->persist($newAlias);
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
		return UnitRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return PhysicalQuantityRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['physicalQuantity-id', 'physicalQuantity'];
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        // TODO: Implement checkParentValidity() method.
    }
}
