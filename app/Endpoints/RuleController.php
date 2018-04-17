<?php

namespace App\Controllers;

use App\Entity\
{
	Classification, Repositories\RuleRepository, Repositories\RuleRepositoryImpl, RuleAnnotation, RuleClassification, Rule, Organism, RuleStatus
};
use App\Exceptions\
{
	ApiException, InternalErrorException, NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Container;
use Slim\Http\{Request, Response};

//TODO: change to WritableController, fix saving
final class RuleController extends ReadableController
{
	use PageableController;
	use SortableController;

	/** @var RuleRepository */
	private $repository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->repository = new RuleRepositoryImpl($c['em']);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'type', 'code'];
	}

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
		$numResults = $this->repository->getNumResults([]);
		$limit = self::getPaginationData($args, $numResults);
		$response = $response->withHeader('X-Pages', $limit['pages']);
		return self::formatOk($response, $this->repository->getList([], self::getSort($args), $limit));
	}

	protected function createEntity(ArgumentParser $data): Rule
	{
		return new Rule;
	}

	/**
	 * @param int $id
	 * @return Rule
	 * @throws ApiException
	 */
	protected function getEntity(int $id)
	{
		try {
			$ent = $this->repository->get($id);
			if (!$ent)
				throw new NonExistingObjectException($id, 'rule');
		}
		catch (ORMException $e) {
			throw new InternalErrorException('Failed getting rule ID ' . $id, $e);
		}

		return $ent;
	}

	/**
	 * @param Rule $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'name' => $entity->getName(),
			'equation' => $entity->getEquation(),
			'code' => $entity->getCode(),
			'modifier' => $entity->getModifier(),
			'status' => (string)$entity->getStatus(),
		];
	}

	/**
	 * @param Rule $entity
	 * @return array
	 */
	protected function getSingleData($entity): array
	{
		return [
				'classifications' => $entity->getClassifications()->map(function(RuleClassification $classification)
				{
					return $classification->getId();
				})->toArray(),
				'organisms' => $entity->getOrganisms()->map(function(Organism $organism)
				{
					return $organism->getId();
				})->toArray(),
				'annotations' => $entity->getAnnotations()->map(function(RuleAnnotation $annotation)
				{
					return ['id' => $annotation->getTermId(), 'type' => $annotation->getTermType()];
				})->toArray(),
			];
	}

	/**
	 * @param Rule $entity
	 * @param ArgumentParser $data
	 * @throws \Exception
	 */
	protected function setData($entity, ArgumentParser $data): void
	{
		if ($data->hasKey('name'))
			$entity->setName($data->getString('name'));
		if ($data->hasKey('code'))
			$entity->setCode($data->getString('code'));
		if ($data->hasKey('description'))
			$entity->setDescription($data->getString('description'));
		if ($data->hasKey('status'))
			$entity->setStatus(RuleStatus::fromInt($data->getInt('status')));
	}
}
