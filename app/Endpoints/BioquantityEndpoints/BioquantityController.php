<?php

namespace App\Controllers;

use App\Entity\{Bioquantity,
    BioquantityMethod,
    IdentifiedObject,
    BioquantityVariable,
    Organism,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\BioquantityRepository};
use App\Exceptions\{
	DependentResourcesBoundException,
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read Repository $repository
 * @method Bioquantity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class BioquantityController extends EBaseController
{
	/** @var BioquantityRepository */
	private $bioquantityRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->bioquantityRepository = $c->get(BioquantityRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id, name, isValid, isAutomatic'];
	}

	protected function getData(IdentifiedObject $bioquantity): array
	{
		/** @var Bioquantity $bioquantity */
		$sBaseData = parent::getData($bioquantity);
		if($bioquantity != null) {
            return array_merge($sBaseData, [
                'userId' => $bioquantity->getUserId(),
                'name' => $bioquantity->getName(),
                'description' => $bioquantity->getDescription(),
                'isAutomatic' => $bioquantity->getIsAutomatic(),
                'isValid' => $bioquantity->getIsValid(),
                'organism' => $bioquantity->getOrganismId()!= null ? OrganismController::getData($bioquantity->getOrganismId()):null,
                'methods' => $bioquantity->getMethods()->map(function (BioquantityMethod $method) {
                    return ['id' => $method->getId(), 'value' => $method->getValue()];
                })->toArray()
            ]);
        }
	}

	protected function setData(IdentifiedObject $bioquantity, ArgumentParser $data): void
	{
		/** @var Bioquantity $bioquantity */
		parent::setData($bioquantity, $data);
		//!$data->hasKey('userId') ?: $experiment->setUserId($data->getInt('userId'));
		!$data->hasKey('name') ?: $bioquantity->setName($data->getString('name'));
		!$data->hasKey('isValid') ?: $bioquantity->setIsValid($data->getBool('isAutomatic'));
		!$data->hasKey('isAutomatic') ?: $bioquantity->setIsAutomatic($data->getBool('isValid'));
		!$data->hasKey('description') ?: $bioquantity->setDescription($data->getString('description'));
		!$data->hasKey('organismId') ?: $bioquantity->setOrganismId($data->getInt('organismId'));
		!$data->hasKey('unitId') ?: $bioquantity->setUnitId($data->getInt('unitId'));
		!$data->hasKey('entityId') ?: $bioquantity->setEntityId($data->getString('status'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
	    //Zatim neni userId
		/*if (!$body->hasKey('userId'))
			throw new MissingRequiredKeyException('userId');*/
		return new Bioquantity;
	}

	protected function checkInsertObject(IdentifiedObject $bioquantity): void
	{
		/** @var Bioquantity $bioquantity */
		/*if ($experiment->getUserId() === null)
			throw new MissingRequiredKeyException('userId');*/
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var Bioquantity $bioquantity */
		$bioquantity = $this->getObject($args->getInt('id'));
		if (!$bioquantity->getMethods()->isEmpty())
			throw new DependentResourcesBoundException('methods');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			//'userId' => new Assert\Type(['type' => 'integer']),
			'description' => new Assert\Type(['type' => 'string']),
			//'visualisation' => new Assert\Type(['type' => 'string']),
			'IsValid' => new Assert\Type(['type' => 'bool']),
            'IsAutomatic' => new Assert\Type(['type' => 'bool']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'bioquantity';
	}

	protected static function getRepositoryClassName(): string
	{
		return BioquantityRepository::Class;
	}
}
