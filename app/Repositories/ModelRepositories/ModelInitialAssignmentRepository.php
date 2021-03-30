<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\IdentifiedObject;
use App\Entity\ModelInitialAssignment;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;

class ModelInitialAssignmentRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

    /** @var IdentifiedObject */
    private $object;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelInitialAssignment::class);
	}

	protected static function getParentClassName(): string
	{
		return Model::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelInitialAssignment::class, $id);
	}

    protected static function alias(): string
    {
        return 'i';
    }

	public function getNumResults(array $filter): int
	{
        return $this->repository
            ->matching($this->createQueryCriteria($filter))
            ->count();
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
        return $this->repository
            ->matching($this->createQueryCriteria($filter, $limit, $sort))
            ->map(function (ModelInitialAssignment $event) {
                return [
                    'id' => $event->getId(),
                    'alias' => $event->getAlias(),
                    'name' => $event->getName(),
                    'ontologyTerm' => $event->getSboTerm(),
                    'notes' => $event->getNotes(),
                    'expression' => [
                        'latex' => is_null($event->getExpression()) ? '' : $event->getExpression()->getLatex(),
                        'cmml' => is_null($event->getExpression()) ? '' : $event->getExpression()->getContentMML()]
                ];
            })->toArray();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->object;
	}

    /**
     * @param IdentifiedObject $object
     * @throws Exception
     */
	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new Exception('Parent of initial assignment must be ' . $className);
		$this->object = $object;
	}

    /**
     * @param array $filter
     * @param array|null $limit
     * @param array|null $sort
     * @return Criteria
     */
    public function createQueryCriteria(array $filter, array $limit = null, array $sort = null): Criteria
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('modelId', $this->object));
        foreach ($filter['argFilter'] as $by => $expr){
            $criteria = $criteria->andWhere(Criteria::expr()->contains($by, $expr));
        }
        return $criteria->setMaxResults($limit['limit'] ? $limit['limit'] : null)
            ->setFirstResult($limit['offset'] ? $limit['offset'] : null)
            ->orderBy($sort ? $sort : []);
    }

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelInitialAssignment::class, 'i')
			->where('i.modelId = :modelId')
			->setParameter('modelId', $this->object->getId());
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
