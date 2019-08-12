<?php

namespace App\Controllers;

use App\Entity\{
    Experiment,
	ExperimentDevice,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ExperimentRepository,
	Repositories\ExperimentDeviceRepository
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
 * @property-read ExperimentDeviceRepository $repository
 * @method ExperimentDevice getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentDeviceController extends ParentedEBaseController
{
	/** @var ExperimentDeviceRepository */
	private $deviceRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->deviceRepository = $v->get(ExperimentDeviceRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['experimentId'];
	}

	protected function getData(IdentifiedObject $device): array
	{
		/** @var ExperimentDevice $device */
		$eBaseData = parent::getData($device);
		return array_merge ($eBaseData, [
			'experimentId' => $device->getExperimentId(),
			'deviceId' => $device->getDeviceId(),
			'experiments' => $device->getExperiments()->map(function (Experiment $experiment) {
				return ['id' => $experiment->getId(), 'name' => $experiment->getName()];
			})->toArray(),
		]);
	}

	protected function setData(IdentifiedObject $device, ArgumentParser $data): void
	{
		/** @var ExperimentDevice $device */
		parent::setData($device, $data);
		$device->getExperimentId() ?: $device->setExperimentId($this->repository->getParent());
		//!$data->hasKey('experimentId') ?: $device->setExperimentId($data->getInt('experimentId'));
		!$data->hasKey('deviceId') ?: $device->setDeviceId($data->getInt('deviceId'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('experimentId'))
			throw new MissingRequiredKeyException('experimentId');
		if (!$body->hasKey('deviceId'))
			throw new MissingRequiredKeyException('deviceId');
		return new ExperimentDevice;
	}

	protected function checkInsertObject(IdentifiedObject $variable): void
	{
		/** @var ExperimentDevice $device */
		if ($device->getExperimentId() === null)
			throw new MissingRequiredKeyException('experimentId');
		if ($device->getDeviceId() === null)
			throw new MissingRequiredKeyException('deviceId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'experimentId' => new Assert\Type(['type' => 'integer']),
            'deviceId' => new Assert\Type(['type' => 'integer']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'experimentDevice';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentDeviceRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ExperimentRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['experiment-id', 'experiment'];
	}
}
