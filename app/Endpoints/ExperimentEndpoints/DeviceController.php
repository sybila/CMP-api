<?php

namespace App\Controllers;

use App\Entity\{
    Experiment,
    Device,
	ExperimentDevice,
	IdentifiedObject,
	Repositories\IEndpointRepository,
	Repositories\ExperimentRepository,
    Repositories\DeviceRepository,
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
 * @property-read DeviceRepository $repository
 * @method ExperimentDevice getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class DeviceController extends ParentedEBaseController
{
	/** @var DeviceRepository */
	private $deviceRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->deviceRepository = $v->get(DeviceRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['deviceId', 'name', 'type', 'address'];
	}

	protected function getData(IdentifiedObject $device): array
	{
		/** @var Device $device */
		$eBaseData = parent::getData($device);
		return array_merge ($eBaseData, [
			'type' => $device->getType(),
			'name' => $device->getName(),
			'address' => $device->getAddress(),
		]);
	}

	protected function setData(IdentifiedObject $device, ArgumentParser $data): void
	{
		/** @var Device $device */
		parent::setData($device, $data);
        !$data->hasKey('name') ?: $device->setName($data->getString('name'));
        !$data->hasKey('type') ?: $device->setType($data->getString('type'));
        !$data->hasKey('address') ?: $device->setAddress($data->getInt('inserted'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('id'))
			throw new MissingRequiredKeyException('id');
        if (!$body->hasKey('name'))
            throw new MissingRequiredKeyException('name');
		return new Device;
	}

	protected function checkInsertObject(IdentifiedObject $variable): void
	{
		/** @var Device $device */
		if ($device->getName() === null)
			throw new MissingRequiredKeyException('experimentId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'name' => new Assert\Type(['type' => 'string']),
            //'deviceId' => new Assert\Type(['type' => 'integer']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'device';
	}

	protected static function getRepositoryClassName(): string
	{
		return DeviceRepository::Class;
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
