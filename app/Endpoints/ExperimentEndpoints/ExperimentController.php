<?php

namespace App\Controllers;

use App\Entity\{Experiment,
    ExperimentModels,
    IdentifiedObject,
    ExperimentVariable,
    ExperimentRelation,
    ExperimentDevice,
    ExperimentNote,
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
final class ExperimentController extends EBaseController
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
		$sBaseData = parent::getData($experiment);
		if($experiment != null) {
            return array_merge($sBaseData, [
                'userId' => $experiment->getUserId(),
                'name' => $experiment->getName(),
                'description' => $experiment->getDescription(),
                'inserted' => $experiment->getInserted(),
                'started' => $experiment->getStarted(),
                'status' => (string)$experiment->getStatus(),
                'organism' => $experiment->getOrganismId()!= null ? OrganismController::getData($experiment->getOrganismId()):null,
                'variables' => $experiment->getVariables()->map(function (ExperimentVariable $variable) {
                    return ['id' => $variable->getId(), 'name' => $variable->getName()];
                })->toArray(),
                'notes' => $experiment->getNote()->map(function (ExperimentNote $note) {
                    return ['id' => $note->getId(), 'note' => $note->getNote()];
                })->toArray(),
                'experimentRelation' => $experiment->getExperimentRelation()->map(function (ExperimentRelation $experimentRelation) {
                return [ $experimentRelation->getSecondExperimentId() != null? $this->getData($experimentRelation->getSecondExperimentId()):null];
                })->toArray(),
                'experimentModels' => $experiment->getExperimentModels()->map(function (ExperimentModels $experimentModels) {
                    return [ $experimentModels->getModelRelationExperimentId() != null? ModelController::getData($experimentModels->getModelRelationExperimentId()):null];
                })->toArray(),
                /*'devices' => $experiment->getExperimentDevices()->map(function (ExperimentDevice $devices) {
                return ['experimentId' => $devices->getExperimentId(), 'deviceId' => $devices->getDeviceId()()];
                })->toArray(),*/
            ]);
        }
	}

	protected function setData(IdentifiedObject $experiment, ArgumentParser $data): void
	{
		/** @var Experiment $experiment */
		parent::setData($experiment, $data);
		//!$data->hasKey('userId') ?: $experiment->setUserId($data->getInt('userId'));
		!$data->hasKey('name') ?: $experiment->setName($data->getString('name'));
		!$data->hasKey('started') ?: $experiment->setStarted($data->getString('started'));
		!$data->hasKey('inserted') ?: $experiment->setInserted($data->getString('inserted'));
		!$data->hasKey('description') ?: $experiment->setDescription($data->getString('description'));
		!$data->hasKey('organismId') ?: $experiment->setOrganismId($data->getInt('organismId'));
		!$data->hasKey('protocol') ?: $experiment->setProtocol($data->getString('protocol'));
		!$data->hasKey('status') ?: $experiment->setStatus($data->getString('status'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		/*if (!$body->hasKey('userId'))
			throw new MissingRequiredKeyException('userId');*/
		return new Experiment;
	}

	protected function checkInsertObject(IdentifiedObject $experiment): void
	{
		/** @var Experiment $experiment */
		/*if ($experiment->getUserId() === null)
			throw new MissingRequiredKeyException('userId');*/
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
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			//'userId' => new Assert\Type(['type' => 'integer']),
			'description' => new Assert\Type(['type' => 'string']),
			//'visualisation' => new Assert\Type(['type' => 'string']),
			'status' => new Assert\Type(['type' => 'string']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'experiment';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentRepository::Class;
	}

	protected function getSub($entity)
	{
		echo $entity;
	}

}
