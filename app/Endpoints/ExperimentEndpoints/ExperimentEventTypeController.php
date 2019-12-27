<?php

namespace App\Controllers;

use App\Entity\{Experiment,
    ExperimentEventType,
    IdentifiedObject,
    Repositories\ExperimentEventTypeRepository,
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
 * @method ExperimentEventType getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentEventTypeController extends WritableRepositoryController
{
	/** @var ExperimentEventTypeRepository */

    public function __construct(Container $c)
	{
		parent::__construct($c);
	}

	protected static function getAllowedSort(): array
	{
		return ['id, type'];
	}

	protected function getData(IdentifiedObject $eventType): array
	{
		/** @var ExperimentEventType $eventType */
		if($eventType != null) {
            return  [
                'id' => $eventType->getId(),
                'type' => $eventType->getType(),
            ];
        }
	}

	protected function setData(IdentifiedObject $eventType, ArgumentParser $data): void
	{
		/** @var ExperimentEventType $eventType */
		!$data->hasKey('type') ?: $eventType->setType($data->getString('type'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
        if (!$body->hasKey('type'))
            throw new MissingRequiredKeyException('type');
		return new ExperimentEventType();
	}

	protected function checkInsertObject(IdentifiedObject $eventType): void
	{
        /** @var ExperimentEventType $eventType */
        if ($eventType->getType() === null)
            throw new MissingRequiredKeyException('type');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
        /** @var ExperimentEventType $eventType */
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
		return 'eventType';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentEventTypeRepository::Class;
	}
}
