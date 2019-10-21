<?php

namespace App\Controllers;

use App\Entity\{Experiment,
    ExperimentModels,
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
final class ExperimentController extends WritableRepositoryController
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
		return ['id, name, userId, status'];
	}

	protected function getData(IdentifiedObject $experiment): array
	{
		/** @var Experiment $experiment */
		if($experiment != null) {
            return  [
                'id' => $experiment->getId(),
                'userId' => $experiment->getUserId(),
                'name' => $experiment->getName(),
                'description' => $experiment->getDescription(),
                'protocol' => $experiment->getProtocol(),
                'inserted' => $experiment->getInserted(),
                'started' => $experiment->getStarted(),
                'status' => (string)$experiment->getStatus(),
                'organism' => $experiment->getOrganismId()!= null ? OrganismController::getData($experiment->getOrganismId()):null,
                'variables' => $experiment->getVariables()->map(function (ExperimentVariable $variable) {
                    return ['id' => $variable->getId(), 'name' => $variable->getName(), 'code' => $variable->getCode(), 'type' => $variable->getType()];
                })->toArray(),
                /*'devices' => $experiment->getDevices()->map(function (Device $device) {
                    return ['id' => $device->getId(), 'name' => $device->getName(), 'address' => $device->getAddress()];
                })->toArray(),*/
                'notes' => $experiment->getNote()->map(function (ExperimentNote $note) {
                    return ['id' => $note->getId(), 'note' => $note->getNote()];
                })->toArray(),
                'experimentRelation' => $experiment->getExperimentRelation()->map(function (ExperimentRelation $experimentRelation) {
                    return [ 'id' => $experimentRelation->getSecondExperimentId()->getId(), 'name' => $experimentRelation->getSecondExperimentId()->getName()];
                })->toArray(),
                'experimentModels' => $experiment->getExperimentModels()->map(function (ExperimentModels $experimentModels) {
                    return [ 'id' => $experimentModels->getModelRelationExperimentId()->getId(), 'name' => $experimentModels->getModelRelationExperimentId()->getName()];
                })->toArray(),
              /* 'devices' => $experiment->getExperimentDevices()->map(function (Device $devices) {
                return ['experimentId' => $devices->getExperimentId(), 'deviceId' => $devices->getDeviceId()()];
                })->toArray(),*/
            ];
        }
	}

	protected function setData(IdentifiedObject $experiment, ArgumentParser $data): void
	{
		/** @var Experiment $experiment */
		//!$data->hasKey('userId') ?: $experiment->setUserId($data->getInt('userId'));
		!$data->hasKey('name') ?: $experiment->setName($data->getString('name'));
		//!$data->hasKey('started') ?: $experiment->setStarted($data->getDateTime('started'));
		//!$data->hasKey('inserted') ?: $experiment->setInserted($data->getDateTime('inserted'));
		!$data->hasKey('description') ?: $experiment->setDescription($data->getString('description'));
		!$data->hasKey('organismId') ?: $experiment->setOrganismId(Organism::getId($data->getInt('organismId')));
		!$data->hasKey('protocol') ?: $experiment->setProtocol($data->getString('protocol'));
		!$data->hasKey('status') ?: $experiment->setStatus($data->getString('status'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
	    //Zatim neni userId
		/*if (!$body->hasKey('user_id'))
			throw new MissingRequiredKeyException('user_id');*/
        if (!$body->hasKey('name'))
            throw new MissingRequiredKeyException('name');
        if (!$body->hasKey('status'))
            throw new MissingRequiredKeyException('status');
		return new Experiment;
	}

	protected function checkInsertObject(IdentifiedObject $experiment): void
	{
		/** @var Experiment $experiment */
		/*if ($experiment->getUserId() === null)
			throw new MissingRequiredKeyException('user_id');*/
        if ($experiment->getName() === null)
            throw new MissingRequiredKeyException('name');
        if ($experiment->getStatus() === null)
            throw new MissingRequiredKeyException('status');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var Experiment $experiment */
		$experiment = $this->getObject($args->getInt('id'));
		if (!$experiment->getVariables()->isEmpty())
			throw new DependentResourcesBoundException('variable');
		if (!$experiment->getNote()->isEmpty())
			throw new DependentResourcesBoundException('note');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			//'userId' => new Assert\Type(['type' => 'integer']),
			'description' => new Assert\Type(['type' => 'string']),
			'status' => new Assert\Type(['type' => 'string']),
		]);
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
