<?php

namespace App\Controllers;

use App\Entity\{
    ExperimentRelation,
    Experiment,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ExperimentRelationRepository};
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
 * @property-read ExperimentRelationRepository $repository
 * @method ExperimentRelation getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentRelationController extends ParentedEBaseController
{
	/** @var ExperimentRelationRepository */
	private $relationRepository;

	public function __construct(Container $v)
	{
		parent::__construct($v);
		$this->relationRepository = $v->get(ExperimentRelationRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['secondExperimentId'];
	}

	protected function getData(IdentifiedObject $relation): array
	{
		/** @var ExperimentRelation $relation */
		$eBaseData = parent::getData($relation);
		return array_merge ($eBaseData, [
			'firstExperimentId' => $relation->getFirstExperimentid(),
            'secondExperimentId' => $relation->getSecondExperimentid(),
            'relatedExperiments' => $relation->getRelatedExperiment()->map(function (Experiment $relatedExperiment) {
                return ['id' => $relatedExperiment->getId(), 'name' => $relatedExperiment->getName()];
            })->toArray(),
		]);
	}

	protected function setData(IdentifiedObject $relation, ArgumentParser $data): void
	{
		/** @var ExperimentRelation $relation */
		parent::setData($relation, $data);
		$relation->getFirstExperimentId() ?: $relation->setFirstExperimentId($this->repository->getParent());
		!$data->hasKey('secondExperimentId') ?: $relation->setSecondExperimentId($data->getInt('secondExperimentId'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		/*if (!$body->hasKey('firstExperimentId'))
			throw new MissingRequiredKeyException('firstExperimentId');*/
		if (!$body->hasKey('secondExperimentId'))
			throw new MissingRequiredKeyException('secondExperimentId');
		return new ExperimentRelation;
	}

	protected function checkInsertObject(IdentifiedObject $relation): void
	{
		/** @var ExperimentRelation $relation */
		if ($relation->getFirstExperimentId() === null)
			throw new MissingRequiredKeyException('firsExperimentId');
		if ($relation->getSecondExperimentId() === null)
			throw new MissingRequiredKeyException('secondExperimentId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var ExperimentRelation $relation */
		$relation = $this->getObject($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			/*'firstExperimentId' => new Assert\Type(['type' => 'integer']),
            'secondExperimentId' => new Assert\Type(['type' => 'integer']),*/
		]));
	}

	protected static function getObjectName(): string
	{
		return 'experimentRelation';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentRelationRepository::Class;
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
