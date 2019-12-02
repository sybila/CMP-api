<?php

namespace App\Controllers;

use App\Entity\{Bioquantity,
    Device,
    Experiment,
    IdentifiedObject,
    Repositories\DeviceRepository,
    Repositories\ExperimentRepository,
    Repositories\IEndpointRepository,
    Repositories\UnitRepository,
    Unit};
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
 * @method Unit getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class UnitController extends WritableRepositoryController
{
    /** @var UnitRepository */
    private $unitRepository;

    public function __construct(Container $c)
    {
        parent::__construct($c);
        $this->unitRepository = $c->get(UnitRepository::class);
    }

    protected static function getAllowedSort(): array
    {
        return ['id, name, code'];
    }

    protected function getData(IdentifiedObject $unit): array
    {
        /** @var Unit $unit */
        if($unit != null) {
            return  [
                'id' => $unit->getId(),
                'name' => $unit->getName(),
                'code' => $unit->getCode()];
        }
    }

    protected function setData(IdentifiedObject $unit, ArgumentParser $data): void
    {
        /** @var Unit $unit */
        !$unit->hasKey('name') ?: $unit->setName($data->getString('name'));
        !$unit->hasKey('code') ?: $unit->setCode($data->getString('code'));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        if (!$body->hasKey('name'))
            throw new MissingRequiredKeyException('name');
        if (!$body->hasKey('code'))
            throw new MissingRequiredKeyException('code');
        return new Unit();
    }

    protected function checkInsertObject(IdentifiedObject $unit): void
    {
        /** @var Unit $unit */
        if ($unit->getName() === null)
            throw new MissingRequiredKeyException('name');
    }

    public function delete(Request $request, Response $response, ArgumentParser $args): Response
    {
        return parent::delete($request, $response, $args);
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Type(['type' => 'string']),
            'code' => new Assert\Type(['type' => 'string']),
        ]);
    }

    protected static function getObjectName(): string
    {
        return 'unit';
    }

    protected static function getRepositoryClassName(): string
    {
        return UnitRepository::Class;
    }
}