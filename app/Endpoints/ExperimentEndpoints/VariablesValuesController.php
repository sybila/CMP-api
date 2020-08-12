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

	protected function getDataInnerPaging(IdentifiedObject $experiment, ArgumentParser $args): array
    {
        /** @var Experiment $experiment */
        if($experiment != null) {
            $data_response =  [
                'variables' => $experiment->getVariables()->map(function (ExperimentVariable $variable) use ($args) {
                    return [
                        'id' => $variable->getId(),
                        'name' => $variable->getName(),
                        'code' => $variable->getCode(),
                        'type' => $variable->getType(),
                        'values' => $variable->getValues()->map(function (ExperimentValues $val) use ($args) {
                            return [
                                'time' => $val->getTime(),
                                'value' => $val->getValue()
                            ];})->toArray(),
                    ];
                })->toArray(),
            ];
            $i = 0;
            $numResult = 0;
            foreach ($data_response['variables'] as $p_var) {
                $paginated_data = $p_var['values'];
                $numResult = count($paginated_data) > $numResult ? count($paginated_data) :  $numResult;
                $data_response['variables'][$i]['values'] = array_slice($paginated_data,
                    ($args['page'] - 1) * $args['perPage'], $args['perPage']);
                $i = $i + 1;
            }
            $data['data'] = $data_response;
            $data['maxCount'] = $numResult;
            return $data;
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
        return 'v';
    }
}
