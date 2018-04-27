<?php

namespace App\Entity\Repositories;

use App\Entity\Annotation;
use App\Entity\AnnotationTerm;
use App\Entity\Entity;
use App\Entity\EntityAnnotation;
use App\Entity\IAnnotatedObject;
use App\Entity\IdentifiedObject;
use App\Entity\Rule;
use App\Entity\RuleAnnotation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;

interface AnnotationRepository extends IDependentEndpointRepository
{
}

abstract class AnnotationRepositoryImpl implements AnnotationRepository
{
	/** @var IAnnotatedObject|IdentifiedObject */
	protected $object;

	/** @var EntityManager */
	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	abstract protected static function getClassName(): string;
	abstract protected static function getParentClassName(): string;

	protected function buildListQuery(?AnnotationTerm $term): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(static::getClassName(), 'a')
			->where('a.itemId = :id')
			->setParameter('id', $this->object->getId());

		if ($term)
			$query->andWhere('a.termType = :type')
				->setParameter('type', $term->getValue());

		return $query;
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter['term'] ?? null)
			->select('a.id, a.termType, a.termId');

		foreach ($sort as $by => $how)
			$query->addOrderBy('a.' . $by, $how ?: null);

		if ($limit['limit'] > 0)
			$query->setMaxResults($limit['limit'])->setFirstResult($limit['offset']);

		return $query->getQuery()->getArrayResult();
	}

	public function getNumResults(array $filter): int
	{
		return (int)$this->buildListQuery($filter['term'] ?? null)
			->select('COUNT(a)')
			->getQuery()
			->getScalarResult()[0][1];
	}

	public function remove($object): void
	{
		$this->object->removeAnnotation($object);
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of annotation must be ' . $className);

		$this->object = $object;
	}

	/**
	 * @param int $id
	 * @return Annotation|null
	 */
	public function get(int $id)
	{
		return $this->em->getRepository(static::getClassName())->findOneBy([
			'id' => $id,
			'itemId' => $this->object->getId(),
		]);
	}
}

/**
 * @property-read Entity $object
 */
final class EntityAnnotationRepositoryImpl extends AnnotationRepositoryImpl
{
	protected static function getClassName(): string
	{
		return EntityAnnotation::class;
	}

	protected static function getParentClassName(): string
	{
		return Entity::class;
	}

	/**
	 * @param EntityAnnotation $object
	 */
	public function add($object): void
	{
		$object->setEntity($this->object);
	}
}

/**
 * @property-read Rule $object
 */
final class RuleAnnotationRepositoryImpl extends AnnotationRepositoryImpl
{
	protected static function getClassName(): string
	{
		return RuleAnnotation::class;
	}

	protected static function getParentClassName(): string
	{
		return Rule::class;
	}

	/**
	 * @param RuleAnnotation $object
	 */
	public function add($object): void
	{
		$object->setRule($this->object);
	}
}
