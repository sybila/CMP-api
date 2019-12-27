<?php

namespace App\Controllers;

use App\Entity\{Experiment,
    ExperimentEventType,
    ExperimentEventVarType,
    IdentifiedObject,
    Repositories\ExperimentEventTypeRepository,
    Repositories\ExperimentEventVarTypeRepository,
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
 * @method ExperimentEventVarType getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentEventVarTypeController extends WritableRepositoryController
{
	/** @var ExperimentEventVarTypeRepository */

    public function __construct(Container $c)
	{
		parent::__construct($c);
	}

	protected static function getAllowedSort(): array
	{
		return ['id, type'];
	}

	protected function getData(IdentifiedObject $eventVarType): array
	{
		/** @var ExperimentEventVarType $eventVarType */
		if($eventVarType != null) {
            return  [
                'id' => $eventVarType->getId(),
                'type' => $eventVarType->getType(),
            ];
        }
	}

	protected function setData(IdentifiedObject $eventVarType, ArgumentParser $data): void
	{
		/** @var ExperimentEventVarType $eventVarType */
		!$data->hasKey('type') ?: $eventVarType->setType($data->getString('type'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
        if (!$body->hasKey('type'))
            throw new MissingRequiredKeyException('type');
		return new ExperimentEventVarType();
	}

	protected function checkInsertObject(IdentifiedObject $eventVarType): void
	{
        /** @var ExperimentEventVarType $eventVarType */
        if ($eventVarType->getType() === null)
            throw new MissingRequiredKeyException('type');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'type' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected static function getObjectName(): string
	{
		return 'eventVarType';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentEventVarTypeRepository::Class;
	}
}
