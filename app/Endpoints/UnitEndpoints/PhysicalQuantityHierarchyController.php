<?php

namespace App\Controllers;

use App\Entity\{IdentifiedObject,
    PhysicalQuantityHierarchy,
    Repositories\IEndpointRepository,
    Repositories\PhysicalQuantityHierarchyRepository,
    Repositories\PhysicalQuantityRepository};
use App\Exceptions\{MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;
use UnitEndpointAuthorizable;

/**
 * @property-read PhysicalQuantityHierarchyRepository $repository
 * @method PhysicalQuantityHierarchy getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class PhysicalQuantityHierarchyController extends ParentedRepositoryController
{

    use UnitEndpointAuthorizable;

	/** @var PhysicalQuantityHierarchyRepository */
	private $physicalQuantityHierarchyRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->physicalQuantityHierarchyRepository = $v->get(PhysicalQuantityHierarchyRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'function'];
	}

	protected function getData(IdentifiedObject $quantityPhysicalHierarchy): array
	{
		/** @var PhysicalQuantityHierarchy $physicalQuantityHierarchy */
		return [
		    'id' => $physicalQuantityHierarchy->getId(),
			'function' => $physicalQuantityHierarchy->getFunction()
		];
	}

	protected function setData(IdentifiedObject $physicalQuantityHierarchy, ArgumentParser $data): void
	{
		/** @var  PhysicalQuantityHierarchy $physicalQuantityHierarchy */
		$physicalQuantityHierarchy->getId() ?: $physicalQuantityHierarchy->setQuantityId($this->repository->getParent()->getId());
		!$data->hasKey('function') ?: $physicalQuantityHierarchy->setFunction($data->getString('function'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('function'))
			throw new MissingRequiredKeyException('function');
		return new PhysicalQuantityHierarchy();
	}

	protected function checkInsertObject(IdentifiedObject $physicalQuantityHierarchy): void
	{
		/** @var PhysicalQuantityHierarchy $physicalQuantityHierarchy */
		if ($physicalQuantityHierarchy->getQuantityId() === null)
			throw new MissingRequiredKeyException('id');
		if ($physicalQuantityHierarchy->getFunction() === null)
			throw new MissingRequiredKeyException('function');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
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
		return 'physicalQuantityHierarchy';
	}

	protected static function getRepositoryClassName(): string
	{
		return PhysicalQuantityHierarchyRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return PhysicalQuantityRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['physicalQuantity-id', 'physicalQuantity'];
	}

    protected static function getAlias(): string
    {
        return 'h';
    }

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        // TODO: Implement checkParentValidity() method.
    }
}
