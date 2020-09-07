<?php

namespace App\Controllers;

use App\Entity\{
    ExperimentValues,
    ExperimentVariable,
    Experiment,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ExperimentVariableRepository,
    Repositories\ExperimentValueRepository
};

use App\Exceptions\
{
	MissingRequiredKeyException,
	DependentResourcesBoundException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ExperimentValueRepository $repository
 * @method ExperimentValues getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentValueController extends ParentedRepositoryController
{

	/** @var ExperimentValueRepository */
	private $valueRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->valueRepository = $c->get(ExperimentValueRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'time', 'value'];
	}


	protected function getData(IdentifiedObject $value): array
	{
		/** @var ExperimentValues $value */
		return [
			'time' => $value->getTime(),
			'value' => $value->getValue(),
		];
	}

	protected function setData(IdentifiedObject $value, ArgumentParser $data): void
	{
		/** @var ExperimentValues $value */
		$value->getVariableId() ?: $value->setVariableId($this->repository->getParent());
		!$data->hasKey('time') ?: $value->setTime($data->getFloat('time'));
		!$data->hasKey('value') ?: $value->setValue($data->getFloat('value'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
        if (!$body->hasKey('time'))
			throw new MissingRequiredKeyException('time');
		if (!$body->hasKey('value'))
			throw new MissingRequiredKeyException('value');
		return new ExperimentValues;
	}

	protected function checkInsertObject(IdentifiedObject $value): void
	{
		/** @var ExperimentValues $value */
		if ($value->getVariableId() === null)
			throw new MissingRequiredKeyException('variableId');
		if ($value->getTime() === null)
			throw new MissingRequiredKeyException('time');
		if ($value->getValue() === null)
			throw new MissingRequiredKeyException('value');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$value = $this->getObject($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
            //'value' => new Assert\Type(['type' => 'float']),
			//'time' => new Assert\Type(['type' => 'double']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'value';
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

    protected static function getAlias(): string
    {
        return 'v';
    }
}
