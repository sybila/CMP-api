<?php

namespace App\Controllers;

use App\Entity\{
	Attribute,
	Bioquantity,
	IdentifiedObject,
	PhysicalQuantity,
	Repositories\AttributeRepository,
	Repositories\IEndpointRepository,
	Repositories\PhysicalQuantityRepository,
	Repositories\UnitRepository,
	Unit
};
use Doctrine\Common\Collections\ArrayCollection;
use App\Exceptions\{
	MissingRequiredKeyException,
	WrongParentException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request,
	Response
};
use Symfony\Component\Validator\Constraints as Assert;
use UnitEndpointAuthorizable;
use function Composer\Autoload\includeFile;

/**
 * @property-read AttributeRepository $repository
 * @method Attribute getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class AttributeController extends ParentedRepositoryController
{

	use UnitEndpointAuthorizable;

	/** @var AttributeRepository */
	private $attributeRepository;

	private $unitRepository;

	private $quantityRepository;


	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->attributeRepository = $v->get(AttributeRepository::class);
		$this->unitRepository = $v->get(UnitRepository::class);
		$this->quantityRepository = $v->get(PhysicalQuantityRepository::class);
	}


	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'note'];
	}


	protected function getData(IdentifiedObject $attribute): array
	{
		/** @var Attribute $attribute */
		$includeUnits = ($attribute->getQuantityId() !== null) ?
			$this->quantityRepository->get($attribute->getQuantityId()->getId())->getUnits()->filter(function (Unit $unit) use($attribute) {
				return !$attribute->getExcludedUnits()->contains($unit);
			}) : new ArrayCollection();
		$includeUnitsFormatted = $includeUnits->map(function (Unit $unit) {
				return [
					'id' => $unit->getId(),
					'preferred_name' => $unit->getPreferredName(),
					'coefficient' => $unit->getCoefficient(),
					'sbmlId' => $unit->getSbmlId()];
			})->toArray();
		return [
			'id' => $attribute->getId(),
			'name' => $attribute->getName(),
			'note' => $attribute->getNote(),
			'units' => $includeUnitsFormatted,
			'bioquantities' => $attribute->getBioquantities()->map(function (Bioquantity $bioquantities) {
					return ['id' => $bioquantities->getId(), 'name' => $bioquantities->getName()];
				})->toArray(),
		];
	}


	protected function setData(IdentifiedObject $attribute, ArgumentParser $data): void
	{
		/** @var Attribute $attribute */
		$attribute->getQuantityId() ?: $attribute->setQuantityId($this->repository->getParent());
		!$data->hasKey('name') ?: $attribute->setName($data->getString('name'));
		!$data->hasKey('note') ?: $attribute->setNote($data->getString('note'));
		!$data->hasKey('excludeUnit') ?: $attribute->excludeUnit($this->unitRepository->get($data->getInt('excludeUnit')));
		!$data->hasKey('includeUnit') ?: $attribute->includeUnit($this->unitRepository->get($data->getInt('includeUnit')));
	}


	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('name'))
			throw new MissingRequiredKeyException('name');

		return new Attribute();
	}


	protected function checkInsertObject(IdentifiedObject $attribute): void
	{
		/** @var Attribute $attribute */
		if ($attribute->getQuantityId() === null)
			throw new MissingRequiredKeyException('quantityId');
		if ($attribute->getName() === null)
			throw new MissingRequiredKeyException('name');
	}


	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$attribute = $this->getObject($args->getInt('id'));
		if (!$attribute->getExcludedUnits()->isEmpty())
			$attribute->getExcludedUnits()->clear();
		return parent::delete($request, $response, $args);
	}


	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'quantityId' => new Assert\Type(['type' => 'integer']),
		]);
	}


	protected static function getObjectName(): string
	{
		return 'attribute';
	}


	protected static function getRepositoryClassName(): string
	{
		return AttributeRepository::Class;
	}


	protected static function getParentRepositoryClassName(): string
	{
		return PhysicalQuantityRepository::class;
	}

    protected function getParentObjectInfo(): ParentObjectInfo
    {
        return new ParentObjectInfo('physicalQuantity-id',PhysicalQuantity::class);
    }

	protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
	{
		// TODO: Implement checkParentValidity() method.
	}

}
