<?php

namespace App\Controllers;

use App\Entity\{
    ExperimentModels,
    Experiment,
    Model,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ExperimentModelsRepository};
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
 * @property-read ExperimentModelsRepository $repository
 * @method ExperimentModels getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentModelsController extends ParentedRepositoryController
{
	/** @var ExperimentModelsRepository */
	private $modelsRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->modelsRepository = $v->get(ExperimentModelsRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['ModelId'];
	}

	protected function getData(IdentifiedObject $modelsRelation): array
	{
		/** @var ExperimentModels $modelsRelation */
		return [
			'ExperimentId' => $modelsRelation->getExperimentRelationModelId(),
            'ModelId' => $modelsRelation->getModelRelationExperimentId(),
            'relatedModels' => $modelsRelation->getRelatedModels()->map(function (Model $relatedModels) {
                return ['id' => $relatedModels->getId(), 'name' => $relatedModels->getName()];
            })->toArray(),
		];
	}

	protected function setData(IdentifiedObject $modelRelation, ArgumentParser $data): void
	{
		/** @var ExperimentModels $modelRelation */
		parent::setData($modelRelation, $data);
		$modelRelation->getExperimentRelationModelId() ?: $modelRelation->setExperimentRelationModelId($this->repository->getParent());
		!$data->hasKey('ModelId') ?: $modelRelation->setModelRelationExperimentId($data->getInt('secondExperimentId'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		/*if (!$body->hasKey('firstExperimentId'))
			throw new MissingRequiredKeyException('firstExperimentId');*/
		if (!$body->hasKey('ModelId'))
			throw new MissingRequiredKeyException('ModelId');
		return new ExperimentModels;
	}

	protected function checkInsertObject(IdentifiedObject $modelRelation): void
	{
		/** @var ExperimentModels $modelRelation */
		if ($modelRelation->getExperimentRelationModelId() === null)
			throw new MissingRequiredKeyException('ExperimentId');
		if ($modelRelation->getModelRelationExperimentId() === null)
			throw new MissingRequiredKeyException('ModelId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var ExperimentModels $modelRelation */
		$modelRelation = $this->getObject($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'ExperimentId' => new Assert\Type(['type' => 'integer']),
            //'ModelId' => new Assert\Type(['type' => 'integer']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelsRelation';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentModelsRepository::Class;
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
