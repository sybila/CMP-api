<?php

namespace App\Entity\Repositories;

use App\Entity\Experiment;
use App\Entity\ExperimentNote;
use App\Entity\ExperimentVariable;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentVariableNoteRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\NoteRepository */
	private $repository;

	/** @var ExperimentVariable */
	private $variable;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ExperimentNote::class);
	}

	protected static function getParentClassName(): string
	{
		return ExperimentVariable::class;
	}

    protected static function alias(): string
    {
        return 'n';
    }

	public function get(int $id)
	{
		return $this->em->find(ExperimentNote::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(n)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('n.id, n.time, n.note, n.imgLink');
        $query = $this->addPagingDql($query, $limit);
	    $query = $this->addSortDql($query, $sort);
	    return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->variable;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of note must be ' . $className);
		$this->variable = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ExperimentNote::class, 'n')
			->where('n.variableId = :variableId')
			->setParameter('variableId', $this->variable->getId());
        $query = $this->addFilterDql($query, $filter);
		return $query;
	}

    public function add($object): void
    {
    }

    public function remove($object): void
    {
    }
}
