<?php

namespace App\Entity\Repositories;

use App\Entity\ModelSpecie;
use App\Entity\ModelCompartment;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelSpecieRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	/** @var ModelCompartment */
	private $compartment;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelSpecie::class);
	}

	public function getBySbmlId(string $sbmlId)
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

    protected static function alias(): string
    {
        return 's';
    }

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(s)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('s.id, s.name, s.sbmlId, s.sboTerm, s.notes, s.annotation, s.initialExpression, s.hasOnlySubstanceUnits, s.isConstant, s.boundaryCondition');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
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
        $query = $this->addFilterDql($query, $filter);
		return $query;
	}

    public function add($object): void
    {
        // TODO: Implement add() method.
    }

    public function remove($object): void
    {
        // TODO: Implement remove() method.
    }
}
