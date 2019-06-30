<?php

namespace App\Controllers;

use App\Entity\{
	ExperimentValues,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ExperimentRepository,
	Repositories\ExperimentVariableRepository
};
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
 * @property-read ExperimentValueRepository $repository
 * @method ExperimentValue getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentValueController extends ParentedSBaseController
{
	/** @var ExperimentValueRepository */
	private $valueRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->variableRepository = $c->get(ExperimentValueRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'time'];
	}

	protected function getData(IdentifiedObject $value): array
	{
		/** @var ExperimentVariable $value */
		$sBaseData = parent::getData($value);
		return array_merge ($sBaseData, [
			'time' => $value->getTime(),
			'value' => $value->getValue(),
		]);
	}

	protected function setData(IdentifiedObject $value, ArgumentParser $data): void
	{
		/** @var ExperimentValue $value */
		parent::setData($value, $data);
		$value->getVariableId() ?: $value->setVariableId($this->repository->getParent());
		!$data->hasKey('time') ?: $value->setTime($data->getData('time'));
		!$data->hasKey('value') ?: $value->setValue($data->getData('value'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('time'))
			throw new MissingRequiredKeyException('time');
		if (!$body->hasKey('value'))
			throw new MissingRequiredKeyException('value');
		return new ExperimentValue;
	}

	protected function checkInsertObject(IdentifiedObject $variable): void
	{
		/** @var ExperimentValue $value */
		if ($value->getVariableId() === null)
			throw new MissingRequiredKeyException('variableId');
		if ($value->getValue() === null)
			throw new MissingRequiredKeyException('value');
		if ($value->getTime() === null)
			throw new MissingRequiredKeyException('time');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var ExperimentVariable $variable */
		$variable = $this->getObject($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'variableId' => new Assert\Type(['type' => 'integer']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'experimentValue';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentValueRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ExperimentVariableRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['variable-id', 'variable'];
	}
}
