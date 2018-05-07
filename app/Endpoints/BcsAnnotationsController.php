<?php

namespace App\Controllers;

use App\Entity\Annotation;
use App\Entity\AnnotationTerm;
use App\Entity\EntityAnnotation;
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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read AnnotationRepository $repository
 * @method Annotation getObject(int $id)
 */
abstract class BcsAnnotationsController extends ParentedRepositoryController
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

	protected function getData(IdentifiedObject $object): array
	{
		/** @var Annotation $annotation */
		return [
			'id' => $annotation->getId(),
			'termId' => $annotation->getTermId(),
			'termType' => (string)$annotation->getTermType(),
		];
	}

	protected function getTermType(ArgumentParser $body): AnnotationTerm
	{
		if (!$body->hasKey('termType'))
			throw new MalformedInputException('Annotation TermType must be set!');

		try {
			return AnnotationTerm::get(strtolower($body->getString('termType')));
		}
		catch (InvalidEnumValueException $e) {
			throw new InvalidEnumFieldValueException('termType', $body->getString('termType'), implode(', ', AnnotationTerm::getAvailableValues()));
		}
	}

	protected function setData(IdentifiedObject $annotation, ArgumentParser $body): void
	{
		/** @var Annotation $annotation */
		if ($body->hasKey('termId'))
			$annotation->setTermId($body->getString('termId'));
		if ($body->hasKey('termType'))
			$annotation->setTermType($this->getTermType($body));
	}

	protected static function getObjectName(): string
	{
		return 'annotation';
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'termId' => new Assert\NotBlank(),
			'termType' => new Assert\Choice(array_values(AnnotationTerm::getAvailableValues())),
		]);
	}

	protected function checkInsertObject(IdentifiedObject $annotation): void
	{
		/** @var Annotation $annotation */
		if (!$annotation->getTermId())
			throw new MalformedInputException('Annotation TermId must be set!');
	}
}

/**
 * @property-read EntityRepository $parentRepository
 */
class EntityBcsAnnotationsController extends BcsAnnotationsController
{
	protected static function getRepositoryClassName(): string
	{
		return EntityAnnotationRepositoryImpl::class;
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new EntityAnnotation($this->getTermType($body));
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
class RuleBcsAnnotationsController extends BcsAnnotationsController
{
	protected static function getRepositoryClassName(): string
	{
		return RuleAnnotationRepositoryImpl::class;
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new RuleAnnotation($this->getTermType($body));
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
