<?php

namespace App\Controllers;

use App\Entity\{Bioquantity,
    Experiment,
    ExperimentModels,
    IdentifiedObject,
    ExperimentVariable,
    ExperimentRelation,
    ExperimentDevice,
    ExperimentNote,
    Device,
    Model,
    Organism,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ModelRepository,
    Repositories\OrganismRepository};
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
    private $organismRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->experimentRepository = $c->get(ExperimentRepository::class);
        $this->organismRepository = $c->get(OrganismRepository::class);
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
                'notes' => $experiment->getNote()->map(function (ExperimentNote $note) {
                    return ['id' => $note->getId(), 'note' => $note->getNote()];
                })->toArray(),
                'experimentsInRelation' => $experiment->getExperimentRelation()->map(function (Experiment $experiment) {
                    return [ 'id' => $experiment->getId(), 'name' => $experiment->getName()];
                })->toArray(),
                'models' => $experiment->getExperimentModels()->map(function (Model $model) {
                    return [ 'id' => $model->getId(), 'name' => $model->getName()];
                })->toArray(),
                'bioquantities' => $experiment->getBioquantities()->map(function (Bioquantity $bioquantity) {
                    return ['id' => $bioquantity->getId(), 'name' => $bioquantity->getName(), 'description' => $bioquantity->getDescription()];
                })->toArray(),
                'devices' => $experiment->getDevices()->map(function (Device $device) {
                      return ['id' => $device->getId(), 'name' => $device->getName(), 'address' => $device->getAddress()];
                })->toArray(),
            ];
        }
	}

	protected function setData(IdentifiedObject $experiment, ArgumentParser $data): void
	{
		/** @var Experiment $experiment */
		//!$data->hasKey('userId') ?: $experiment->setUserId($data->getInt('userId'));
		!$data->hasKey('name') ?: $experiment->setName($data->getString('name'));
		!$data->hasKey('started') ?: $experiment->setStarted($data->getString('started'));
		!$data->hasKey('description') ?: $experiment->setDescription($data->getString('description'));
		!$data->hasKey('organismId') ?: $experiment->setOrganismId($this->organismRepository->get($data->getInt('organismId')));
		!$data->hasKey('protocol') ?: $experiment->setProtocol($data->getString('protocol'));
		!$data->hasKey('status') ?: $experiment->setStatus($data->getString('status'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
        if (!$body->hasKey('name'))
            throw new MissingRequiredKeyException('name');
        if (!$body->hasKey('status'))
            throw new MissingRequiredKeyException('status');
		return new Experiment;
	}

	protected function checkInsertObject(IdentifiedObject $experiment): void
	{
		/** @var Experiment $experiment */
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
