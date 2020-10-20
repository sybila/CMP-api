<?php

namespace App\Controllers;

use App\Helpers\ArgumentParser;
use Doctrine\Common\Collections\Criteria;
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
use Slim\Http\Request;
use Slim\Http\Response;

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

    /**
     * @override of BASE readIdentified from repositoryController
     * if more entity details would need paging in future
     * we would need to implement some abstract classes or interfaces.
     * @param Request $request
     * @param Response $response
     * @param ArgumentParser $args
     * @return Response
     * @throws mixed
     */
    public function readIdentified(Request $request, Response $response, ArgumentParser $args): Response
    {
        $this->runEvents($this->beforeRequest, $request, $response, $args);
        $this->permitUser([$this, 'validateDetail'], [$this, 'canDetail']);
        $id = current($this->getReadIds($args));
        $ent = $this->getObject((int)$id);
        return $this->getPaginatedData($ent, $response, $args);
    }

    /**
     * @param IdentifiedObject $experiment
     * @param Response $response
     * @param ArgumentParser $args
     * @return Response
     * @throws mixed
     */
    protected function getPaginatedData(IdentifiedObject $experiment, Response $response, ArgumentParser $args): Response
	{
		/** @var Experiment $experiment */
        //Get max count of data that is a subject to paging.
        $numResult = max($experiment->getVariables()->map(function (ExperimentVariable $var){
            return $var->getValues()->count();
        })->toArray());
        //Get limit, offset and count of pages
        $limit = self::getPaginationData($args, $numResult);
        //Get data with paging
        $data = [];
        if($experiment != null) {
            $data = [
                'variables' => $experiment->getVariables()
                    ->map(function (ExperimentVariable $variable) use ($limit) {
                    return [
                        'id' => $variable->getId(),
                        'name' => $variable->getName(),
                        'code' => $variable->getCode(),
                        'type' => $variable->getType(),
                        'values' => $variable->getValues()->matching(Criteria::create()
                                ->setMaxResults($limit['limit'] ? $limit['limit'] : null)
                                ->setFirstResult($limit['offset'] ? $limit['offset'] : null))
                            ->map(function (ExperimentValues $val){
                            return [
                                'time' => $val->getTime(),
                                'value' => $val->getValue()
                            ];
                    })->toArray(),
                    ];
                })->toArray(),
            ];
        }
        //Set needed headers and return the response.
        $response = $response->withHeader('X-MaxCount', $numResult);
        $response = $response->withHeader('X-Pages', $limit['pages']);
        return self::formatOk($response, $data);
	}

	protected function getData(IdentifiedObject $object): array
    {
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
