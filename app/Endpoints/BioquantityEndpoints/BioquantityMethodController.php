<?php

namespace App\Controllers;

use App\Entity\{BioquantityMethod,
    Bioquantity,
    BioquantityVariable,
    IdentifiedObject,
    Repositories\BioquantityMethodRepository,
    Repositories\BioquantityRepository,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ExperimentVariableRepository};
use App\Exceptions\
{
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
 * @property-read BioquantityMethodRepository $repository
 * @method BioquantityMethod getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class BioquantityMethodController extends ParentedRepositoryController
{
	/** @var BioquantityMethodRepository */
	private $methodRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->methodRepository = $v->get(BioquantityMethodRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'value'];
	}

	protected function getData(IdentifiedObject $method): array
	{
		/** @var BioquantityMethod $method */
		return [
			'value' => $method->getValue(),
			'formula' => $method->getFormula(),
			'source' => $method->getSource(),
			'variables' => $method->getVariables()->map(function (BioquantityVariable $var) {
				return ['id' => $var->getId(), 'name' => $var->getName(), 'value' => $var->getValue()];
			})->toArray(),
		];
	}

	protected function setData(IdentifiedObject $method, ArgumentParser $data): void
	{
		/** @var BioquantityMethod $method */
        $method->getBioquantityId() ?: $method->setBioquantityId($this->repository->getParent());
		!$data->hasKey('value') ?: $method->setValue($data->getFloat('value'));
		!$data->hasKey('formula') ?: $method->setFormula($data->getString('formula'));
		!$data->hasKey('source') ?: $method->setSource($data->getString('source'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('value'))
			throw new MissingRequiredKeyException('value');
		return new BioquantityMethod();
	}

	protected function checkInsertObject(IdentifiedObject $method): void
	{
		/** @var BioquantityMethod $method */
		if ($method->getBioquantityId() === null)
			throw new MissingRequiredKeyException('bioquantityId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var BioquantityMethod $method */
		$method = $this->getObject($args->getInt('id'));
		if (!$method->getVariables()->isEmpty())
			throw new DependentResourcesBoundException('variables');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'bioquantityId' => new Assert\Type(['type' => 'integer']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'bioquantityMethod';
	}

	protected static function getRepositoryClassName(): string
	{
		return BioquantityMethodRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return BioquantityRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['bioquantity-id', 'bioquantity'];
	}
}
