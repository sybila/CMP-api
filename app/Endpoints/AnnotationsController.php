<?php

namespace App\Controllers;

use App\Entity\Annotation;
use App\Entity\AnnotationTerm;
use App\Entity\EntityAnnotation;
use App\Entity\EntityRepository;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnnotationRepository;
use App\Entity\Repositories\EntityAnnotationRepositoryImpl;
use App\Entity\Repositories\EntityRepository;
use App\Entity\Repositories\RuleAnnotationRepositoryImpl;
use App\Entity\Repositories\RuleRepository;
use App\Entity\RuleAnnotation;
use App\Exceptions\InvalidEnumFieldValueException;
use App\Exceptions\MalformedInputException;
use App\Helpers\ArgumentParser;
use Consistence\Enum\InvalidEnumValueException;
use Slim\Container;

/**
 * @property-read AnnotationRepository $repository
 * @method Annotation getObject(int $id)
 */
abstract class AnnotationsController extends ParentedRepositoryController
{
	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->beforeInsert[] = function(Annotation $entity)
		{
			if (!$entity->getTermType() || !$entity->getTermId())
				throw new MalformedInputException('TermType and TermId fields are necessary!');
		};
	}

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
	 * @param Annotation     $entity
	 * @param ArgumentParser $body
	 * @param bool           $insert
	 */
	protected function setData($entity, ArgumentParser $body, bool $insert): void
	{
		if ($insert && (!$body->hasKey('termId') || !$body->hasKey('termType')))
			throw new MalformedInputException('Annotation TermId and TermType must be set!');

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

	protected static function getParentRepositoryClassName(): string
	{
		return EntityRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['entity-id', 'entity'];
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

	protected static function getParentRepositoryClassName(): string
	{
		return RuleRepository::class;
	}

	protected function getParentObjectInfo(): array
	{
		return ['rule-id', 'rule'];
	}
}
