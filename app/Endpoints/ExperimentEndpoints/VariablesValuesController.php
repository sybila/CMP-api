<?php

namespace App\Controllers;

use App\Entity\{Experiment,
    //ExperimentModels,
    ExperimentValues,
    IdentifiedObject,
    ExperimentVariable,
    //ExperimentRelation,
    //ExperimentDevice,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository};
use ExperimentEndpointAuthorizable;
use Slim\Container;

/**
 * @property-read ExperimentRepository $repository
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
		return [];
	}

	protected static function getObjectName(): string
	{
		return 'experiment';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentRepository::Class;
	}


}
