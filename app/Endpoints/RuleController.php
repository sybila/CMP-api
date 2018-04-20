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

/**
 * TODO: make writable
 * @property-read RuleRepository $repository
 * @method Rule getObject(int $id)
 */
final class RuleController extends RepositoryController
{
	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'code'];
	}

	protected function createEntity(ArgumentParser $data): Rule
	{
		return new Rule;
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
			'classifications' => $entity->getClassifications()->map(self::identifierGetter())->toArray(),
			'organisms' => $entity->getOrganisms()->map(self::identifierGetter())->toArray(),
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

	protected static function getRepositoryClassName(): string
	{
		return RuleRepositoryImpl::class;
	}

	protected static function getObjectName(): string
	{
		return 'rule';
	}
}
