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
    Unit};
use App\Exceptions\{DependentResourcesBoundException,
    InvalidArgumentException,
    InvalidAuthenticationException,
    MissingRequiredKeyException};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints as Assert;
use UnitEndpointAuthorizable;

/**
 * @property-read Repository $repository
 * @method PhysicalQuantity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class PhysicalQuantityController extends WritableRepositoryController
{

    use UnitEndpointAuthorizable;

	/** @var PhysicalQuantityRepository */
	private $physicalQuantityRepository;

    public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->physicalQuantityRepository = $c->get(PhysicalQuantityRepository::class);
	}


	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $physicalQuantity): array
	{
		/** @var PhysicalQuantity $physicalQuantity */
		if($physicalQuantity != null) {
            return  [
                'id' => $physicalQuantity->getId(),
                'name' => $physicalQuantity->getName(),
                'hierarchy' => ($physicalQuantity->getHierarchy() !== null) ? ['id' => $physicalQuantity->getHierarchy()->getId(), 'function' => $physicalQuantity->getHierarchy()->getFunction()]: null,
                'units' => $physicalQuantity->getUnits()->map(function (Unit $unit) {
                    return ['id' => $unit->getId(), 'preferred_name' => $unit->getPreferredName(), 'coefficient' => $unit->getCoefficient(), 'sbmlId' => $unit->getSbmlId()];
                })->toArray(),
                'attributes' => $physicalQuantity->getAttributes()->map(function (Attribute $attribute) {
                    return [ 'id' => $attribute->getId(), 'name' => $attribute->getName(), 'note' => $attribute->getNote()];
                })->toArray()
            ];
        }
	}

	protected function setData(IdentifiedObject $physicalQuantity, ArgumentParser $data): void
	{
		/** @var PhysicalQuantity $physicalQuantity */
		!$data->hasKey('name') ?: $physicalQuantity->setName($data->getString('name'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
        if (!$body->hasKey('name'))
            throw new MissingRequiredKeyException('name');
        return new PhysicalQuantity();
	}

	protected function checkInsertObject(IdentifiedObject $physicalQuantity): void
	{
		/** @var PhysicalQuantity $physicalQuantity */
        if ($physicalQuantity->getName() === null)
            throw new MissingRequiredKeyException('name');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
        $physicalQuantity = $this->getObject($args->getInt('id'));
		if (!$physicalQuantity->getAttributes()->isEmpty())
		    $physicalQuantity->getAttributes()->clear();
		if (!$physicalQuantity->getUnits()->isEmpty())
            $physicalQuantity->getUnits()->clear();
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'physicalQuantity';
	}

	protected static function getRepositoryClassName(): string
	{
		return PhysicalQuantityRepository::Class;
	}

}
