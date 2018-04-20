<?php

namespace App\Controllers;

use App\Entity\Annotation;
use App\Entity\AnnotationTerm;
use App\Entity\EntityAnnotation;
use App\Entity\EntityRepository;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnnotationRepository;
use App\Entity\Repositories\EntityAnnotationRepositoryImpl;
use App\Entity\Repositories\EntityRepositoryImpl;
use App\Entity\Repositories\IRepository;
use App\Entity\Repositories\RuleAnnotationRepositoryImpl;
use App\Entity\Repositories\RuleRepository;
use App\Entity\Repositories\RuleRepositoryImpl;
use App\Entity\RuleAnnotation;
use App\Exceptions\InvalidEnumFieldValueException;
use App\Exceptions\MalformedInputException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use Consistence\Enum\InvalidEnumValueException;
use Doctrine\ORM\EntityManager;

/**
 * @property-read AnnotationRepository $repository
 * @method Annotation getObject(int $id)
 */
abstract class AnnotationsController extends ParentedRepositoryController
{
	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getFilter(ArgumentParser $args): array
	{
		if ($args->hasKey('type'))
			return ['type' => $args->getString('type')];

		return [];
	}

	/**
	 * @param Annotation $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'termId' => $entity->getTermId(),
			'termType' => (string)$entity->getTermType(),
		];
	}

	/**
	 * @param Annotation $entity
	 * @param ArgumentParser $body
	 */
	protected function setData($entity, ArgumentParser $body): void
	{
		if ($body->hasKey('termId'))
			$entity->setTermId($body->getString('termId'));
		if ($body->hasKey('termType'))
		{
			try {
				$term = AnnotationTerm::get(strtolower($body->getString('termType')));
				$entity->setTermType($term);
			}
			catch (InvalidEnumValueException $e) {
				throw new InvalidEnumFieldValueException('termType', $body->getString('termType'), implode(', ', AnnotationTerm::getAvailableValues()));
			}
		}
	}

	protected static function getObjectName(): string
	{
		return 'annotation';
	}
}

/**
 * @property-read EntityRepository $parentRepository
 */
class EntityAnnotationsController extends AnnotationsController
{
	protected static function getRepositoryClassName(): string
	{
		return EntityAnnotationRepositoryImpl::class;
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new EntityAnnotation;
	}

	protected function getParentObject(ArgumentParser $args): IdentifiedObject
	{
		if (!$args->hasKey('entity-id'))
			throw new MalformedInputException('Missing key entity-id');

		try {
			return $this->parentRepository->get($args->getInt('entity-id'));
		}
		catch (\Exception $e) {
			throw new NonExistingObjectException($args->getString('rule-id'), 'rule');
		}
	}

	protected static function getParentRepositoryClassName(): string
	{
		return EntityRepositoryImpl::class;
	}
}

/**
 * @property-read RuleRepository $parentRepository
 */
class RuleAnnotationsController extends AnnotationsController
{
	protected static function getRepositoryClassName(): string
	{
		return RuleAnnotationRepositoryImpl::class;
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new RuleAnnotation;
	}

	protected function getParentObject(ArgumentParser $args): IdentifiedObject
	{
		if (!$args->hasKey('rule-id'))
			throw new MalformedInputException('Missing key rule-id');

		try {
			return $this->parentRepository->get($args->getInt('rule-id'));
		}
		catch (\Exception $e) {
			throw new NonExistingObjectException($args->getString('rule-id'), 'rule');
		}
	}

	protected static function getParentRepositoryClassName(): string
	{
		return RuleRepositoryImpl::class;
	}
}
