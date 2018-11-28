<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelParameter;
use App\Entity\ModelReaction;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelParameterRepository implements IDependentEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ModelParameterRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelParameter::class);
	}

	public function getBySbmlId(string $sbmlId): ?ModelParameter
	{
		return $this->repository->findOneBy(['sbmlId' => $sbmlId]);
	}

	protected static function getParentClassName(): array
	{
		return [Model::class, ModelReaction::class];
	}

	public function getParent()
	{
		return $this->object;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelParameter::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(p)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('p.id, p.name, p.sbmlId, p.value, p.isConstant');
		return $query->getQuery()->getArrayResult();
	}

	public function setParent(IdentifiedObject $object): void
	{
		$classNames = static::getParentClassName();
		$errorString = '';
		$index = 0;
		foreach ($classNames as $className) {
			if ($object instanceof $className) {
				$this->object = $object;
				return;
			}
			$index == 0 ?: $errorString .= ' or ';
			$index++;
			$errorString .= $className;
		}
		throw new \Exception('Parent of parameter must be ' . $errorString);
	}

	public function getEntityManager()
	{
		return $this->em;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = null;
		if ($this->object instanceof Model) {
			$query = $this->em->createQueryBuilder()
				->from(ModelParameter::class, 'p')
				->where('p.modelId = :modelId AND p.reactionId IS NULL')
				->setParameter('modelId', $this->object->getId());
		}
		if ($this->object instanceof ModelReaction) {
			$query = $this->em->createQueryBuilder()
				->from(ModelParameter::class, 'p')
				->where('p.reactionId = :reactionId')
				->setParameter('reactionId', $this->object->getId());
		}
		return $query;
	}

	public function add($object): void
	{
		// TODO: Refactor
	}

	public function remove($object): void
	{
		// TODO: Refactor
	}
}
