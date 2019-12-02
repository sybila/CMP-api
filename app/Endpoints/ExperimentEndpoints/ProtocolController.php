<?php

namespace App\Controllers;

use App\Entity\{Bioquantity,
    Device,
    Experiment,
    IdentifiedObject,
    Protocol,
    Repositories\DeviceRepository,
    Repositories\ExperimentRepository,
    Repositories\IEndpointRepository,
    Repositories\ProtocolRepository};
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
final class ProtocolController extends WritableRepositoryController
{
    /** @var ProtocolRepository */
    private $protocolRepository;
    private $experimentRepository;

    public function __construct(Container $c)
    {
        parent::__construct($c);
        $this->protocolRepository = $c->get(ProtocolRepository::class);
        $this->experimentRepository = $c->get(ExperimentRepository::class);
    }

    protected static function getAllowedSort(): array
    {
        return ['id, inserted, protocol'];
    }

    protected function getData(IdentifiedObject $protocol): array
    {
        /** @var Protocol $protocol */
        if($protocol != null) {
            return  [
                'id' => $protocol->getId(),
                'inserted' => $protocol->getInserted(),
                'protocol' => $protocol->getProtocol(),
                'experiments' => $protocol->getExperiments()->map(function (Experiment $experiment) {
                    return ['id' => $experiment->getId(), 'name' => $experiment->getName(), 'description' => $experiment->getDescription()];
                })->toArray(),
            ];
        }
    }

    protected function setData(IdentifiedObject $protocol, ArgumentParser $data): void
    {
        /** @var Protocol $protocol */
        !$data->hasKey('protocol') ?: $protocol->setProtocol($data->getString('protocol'));
        !$data->hasKey('addRelatedExperimentId') ?: $protocol->addExperiment($this->experimentRepository->get($data->getInt('addRelatedExperimentId')));
        !$data->hasKey('removeRelatedExperimentId') ?: $protocol->removeExperiment($this->experimentRepository->get($data->getInt('removeRelatedExperimentId')));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        if (!$body->hasKey('protocol'))
            throw new MissingRequiredKeyException('protocol');
        return new Protocol();
    }

    protected function checkInsertObject(IdentifiedObject $protocol): void
    {
        /** @var Protocol $protocol */
        if ($protocol->getProtocol() === null)
            throw new MissingRequiredKeyException('protocol');
    }

    public function delete(Request $request, Response $response, ArgumentParser $args): Response
    {
        /** @var Protocol $protocol */
        $protocol = $this->getObject($args->getInt('id'));
        if (!$protocol->getExperiments()->isEmpty())
            throw new DependentResourcesBoundException('experiment');
        return parent::delete($request, $response, $args);
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'protocol' => new Assert\Type(['type' => 'string']),
        ]);
    }

    protected static function getObjectName(): string
    {
        return 'protocol';
    }

    protected static function getRepositoryClassName(): string
    {
        return ProtocolRepository::Class;
    }
}