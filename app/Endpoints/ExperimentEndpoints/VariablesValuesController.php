<?php

namespace App\Controllers;

use App\Entity\{Experiment,
    ExperimentModels,
    ExperimentValues,
    IdentifiedObject,
    ExperimentVariable,
    ExperimentRelation,
    ExperimentDevice,
    ExperimentNote,
    Device,
    Organism,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ModelRepository};
use App\Exceptions\{
	DependentResourcesBoundException,
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use ExperimentEndpointAuthorizable;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read Repository $repository
 * @method Experiment getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class VariablesValuesController extends RepositoryController
{

    use ExperimentEndpointAuthorizable;

	/** @var ExperimentRepository */
	private $experimentRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->experimentRepository = $c->get(ExperimentRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['variables'];
	}

	protected function getData(IdentifiedObject $experiment): array
	{
		/** @var Experiment $experiment */
		if($experiment != null) {
            return  [
                'variables' => $experiment->getVariables()->map(function (ExperimentVariable $variable) {
                    return [
                        'id' => $variable->getId(),
                        'name' => $variable->getName(),
                        'code' => $variable->getCode(),
                        'type' => $variable->getType(),
                        'values' => $variable->getValues()->map(function (ExperimentValues $val){
                            return [
                                'time' => $val->getTime(),
                                'value' => $val->getValue()
                            ];
                    })->toArray(),
                    ];
                })->toArray(),
            ];
        }
	}

	protected static function getObjectName(): string
	{
		return 'experiment';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentRepository::Class;
	}

    protected static function getAlias(): string
    {
        return 'e';
    }

}
