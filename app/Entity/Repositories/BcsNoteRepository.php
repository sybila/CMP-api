<?php

namespace App\Entity\Repositories;

use App\Entity\Annotation;
use App\Entity\AnnotationTerm;
use App\Entity\BcsNote;
use App\Entity\Entity;
use App\Entity\EntityAnnotation;
use App\Entity\EntityNote;
use App\Entity\IAnnotatedObject;
use App\Entity\IBcsNoteObject;
use App\Entity\IdentifiedObject;
use App\Entity\Rule;
use App\Entity\RuleAnnotation;
use App\Entity\RuleNote;
use App\Exceptions\InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;

interface BcsNoteRepository extends IDependentEndpointRepository
{
}

abstract class BcsNoteRepositoryImpl implements AnnotationRepository
{
	/** @var IBcsNoteObject|IdentifiedObject */
	protected $object;

	/** @var EntityManager */
	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	abstract protected static function getClassName(): string;
	abstract protected static function getParentClassName(): string;
	abstract protected static function getParentKeyName(): string;

	protected function buildListQuery(): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(static::getClassName(), 'n')
			->where('n.' . static::getParentKeyName() . ' = :object')
			->setParameter('object', $this->object);

		return $query;
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery()
			->select('n.id, n.text, n.user, n.inserted, n.updated');

		foreach ($sort as $by => $how)
			$query->addOrderBy('n.' . $by, $how ?: null);

		if ($limit['limit'] > 0)
			$query->setMaxResults($limit['limit'])->setFirstResult($limit['offset']);

		return $query->getQuery()->getArrayResult();
	}

	public function getNumResults(array $filter): int
	{
		return (int)$this->buildListQuery()
			->select('COUNT(n)')
			->getQuery()
			->getArrayResult()[0][1];
	}

	public function remove($object): void
	{
		$this->object->removeNote($object);
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
	 * @return BcsNote|null
	 */
	public function get(int $id)
	{
		return $this->em->getRepository(static::getClassName())->findOneBy([
			'id' => $id,
			static::getParentKeyName() => $this->object->getId()]
		);
	}
}

/**
 * @property-read Entity $object
 */
final class EntityNoteRepository extends BcsNoteRepositoryImpl
{
	protected static function getClassName(): string
	{
		return EntityNote::class;
	}

	protected static function getParentClassName(): string
	{
		return Entity::class;
	}

	protected static function getParentKeyName(): string
	{
		return 'entity';
	}

	/**
	 * @param EntityNote $object
	 */
	public function add($object): void
	{
		$object->setEntity($this->object);
	}
}

/**
 * @property-read Rule $object
 */
final class RuleNoteRepository extends BcsNoteRepositoryImpl
{
	protected static function getClassName(): string
	{
		return RuleNote::class;
	}

	protected static function getParentClassName(): string
	{
		return Rule::class;
	}

	protected static function getParentKeyName(): string
	{
		return 'rule';
	}

	/**
	 * @param RuleNote $object
	 */
	public function add($object): void
	{
		$object->setRule($this->object);
	}
}
