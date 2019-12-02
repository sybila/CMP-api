<?php

namespace App\Controllers;

use App\Entity\{Bioquantity,
    Device,
    Experiment,
    IdentifiedObject,
    Repositories\DeviceRepository,
    Repositories\ExperimentRepository,
    Repositories\IEndpointRepository};
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
 * @method Device getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class DeviceController extends WritableRepositoryController
{
    /** @var DeviceRepository */
    private $deviceRepository;
    private $experimentRepository;

    public function __construct(Container $c)
    {
        parent::__construct($c);
        $this->deviceRepository = $c->get(DeviceRepository::class);
        $this->experimentRepository = $c->get(ExperimentRepository::class);
    }

    protected static function getAllowedSort(): array
    {
        return ['id, type, name, address'];
    }

    protected function getData(IdentifiedObject $device): array
    {
        /** @var Device $device */
        if($device != null) {
            return  [
                'id' => $device->getId(),
                'name' => $device->getName(),
                'type' => $device->getType(),
                'address' => $device->getAddress(),
                'experiments' => $device->getExperiments()->map(function (Experiment $experiment) {
                    return ['id' => $experiment->getId(), 'name' => $experiment->getName(), 'description' => $experiment->getDescription()];
                })->toArray(),
            ];
        }
    }

    protected function setData(IdentifiedObject $device, ArgumentParser $data): void
    {
        /** @var Device $device */
        !$data->hasKey('name') ?: $device->setName($data->getString('name'));
        !$data->hasKey('type') ?: $device->setType($data->getString('type'));
        !$data->hasKey('address') ?: $device->setAddress($data->getString('address'));
        !$data->hasKey('addRelatedExperimentId') ?: $device->addExperiment($this->experimentRepository->get($data->getInt('addRelatedExperimentId')));
        !$data->hasKey('removeRelatedExperimentId') ?: $device->removeExperiment($this->experimentRepository->get($data->getInt('removeRelatedExperimentId')));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        if (!$body->hasKey('name'))
            throw new MissingRequiredKeyException('name');
        return new Device();
    }

    protected function checkInsertObject(IdentifiedObject $device): void
    {
        /** @var Experiment $experiment */
        if ($device->getName() === null)
            throw new MissingRequiredKeyException('name');
    }

    public function delete(Request $request, Response $response, ArgumentParser $args): Response
    {
        /** @var Device $device */
        $device = $this->getObject($args->getInt('id'));
        if (!$device->getExperiments()->isEmpty())
            throw new DependentResourcesBoundException('experiments');
        return parent::delete($request, $response, $args);
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Type(['type' => 'string']),
        ]);
    }

    protected static function getObjectName(): string
    {
        return 'device';
    }

    protected static function getRepositoryClassName(): string
    {
        return DeviceRepository::Class;
    }
}