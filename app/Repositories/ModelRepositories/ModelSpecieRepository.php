<?php

namespace App\Entity\Repositories;

use App\Entity\ModelSpecie;
use App\Entity\ModelCompartment;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelSpecieRepository implements IDependentSBaseRepository
{
	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\SpecieRepository */
	private $repository;

	/** @var ModelCompartment */
	private $compartment;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelSpecie::class);
	}

	public function getBySbmlId(string $sbmlId): ?ModelSpecie
	{
		return $this->repository->findOneBy(['sbmlId' => $sbmlId]);
	}

	protected static function getParentClassName(): string
	{
		return ModelCompartment::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelSpecie::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(s)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('s.id, s.name, s.sbmlId, s.sboTerm, s.notes, s.annotation, s.equationType, s.initialExpression, s.hasOnlySubstanceUnits, s.isConstant, s.boundaryCondition');
		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->compartment;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of specie must be ' . $className);
		$this->compartment = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelSpecie::class, 's')
			->where('s.compartmentId = :compartmentId')
			->setParameters([
				'compartmentId' => $this->compartment->getId()
			]);
		return $query;
	}

}
