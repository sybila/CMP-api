<?php

namespace App\Controllers;

use App\Entity\{BioquantityVariable,
    ExperimentVariable,
    ExperimentValues,
    ExperimentNote,
    IdentifiedObject,
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
 * @property-read ExperimentVariableRepository $repository
 * @method ExperimentVariable getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentVariableController extends ParentedRepositoryController
{
	/** @var ExperimentVariableRepository */
	private $variableRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->variableRepository = $v->get(ExperimentVariableRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $variable): array
	{
		/** @var ExperimentVariable $variable */
		return [
		    'id' => $variable->getId(),
			'name' => $variable->getName(),
			'code' => $variable->getCode(),
			'type' => $variable->getType(),
            'notes' => $variable->getNote()->map(function (ExperimentNote $note) {
                return ['id' => $note->getId(), 'note' => $note->getNote(), 'time' =>  $note->getTime()];
            })->toArray(),
			'values' => $variable->getValues()->map(function (ExperimentValues $val) {
				return ['id' => $val->getId(), 'time' => $val->getTime(), 'value' => $val->getValue()];
			})->toArray(),
            'bioquantityVariables' => $variable->getBioquantities()->map(function(BioquantityVariable $bio){
                return['varName'=> $bio->getName(), 'timeFrom' => $bio->getTimeFrom(), 'timeTo' => $bio->getTimeTo()];
            })
		];
	}

	protected function setData(IdentifiedObject $variable, ArgumentParser $data): void
	{
		/** @var ExperimentVariable $variable */
		//parent::setData($variable, $data);
		$variable->getExperimentId() ?: $variable->setExperimentId($this->repository->getParent());
		!$data->hasKey('name') ?: $variable->setName($data->getString('name'));
		!$data->hasKey('code') ?: $variable->setCode($data->getString('code'));
		!$data->hasKey('type') ?: $variable->setType($data->getString('type'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('name'))
			throw new MissingRequiredKeyException('name');
		if (!$body->hasKey('code'))
			throw new MissingRequiredKeyException('code');
		return new ExperimentVariable;
	}

	protected function checkInsertObject(IdentifiedObject $variable): void
	{
		/** @var ExperimentVariable $variable */
		if ($variable->getExperimentId() === null)
			throw new MissingRequiredKeyException('experimentId');
		if ($variable->getName() === null)
			throw new MissingRequiredKeyException('name');
		if ($variable->getCode() === null)
			throw new MissingRequiredKeyException('code');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var ExperimentVariable $variable */
		$variable = $this->getObject($args->getInt('id'));
		if (!$variable->getValues()->isEmpty())
            $variable->getValues()->clear();
		if (!$variable->getNote()->isEmpty())
		    $variable->getNote()->clear();
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection( [
			'experimentId' => new Assert\Type(['type' => 'integer']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'experimentVariable';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentVariableRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ExperimentRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['experiment-id', 'experiment'];
	}

    protected static function getAlias(): string
    {
        return 'v';
    }
}
