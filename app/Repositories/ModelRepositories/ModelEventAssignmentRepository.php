<?php

namespace App\Entity\Repositories;

use App\Entity\ModelEvent;
use App\Entity\ModelEventAssignment;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Class ModelEventAssignmentRepository
 * @package App\Entity\Repositories
 * @author Radoslav Doktor & Marek Havlik
 */
class ModelEventAssignmentRepository implements IDependentEndpointRepository
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
		$this->repository = $em->getRepository(ModelEventAssignment::class);
	}

	protected static function getParentClassName(): string
	{
		return ModelEvent::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelEventAssignment::class, $id);
	}

    protected static function alias(): string
    {
        return 'e';
    }

    /**
     * @param array $filter
     * @param array|null $limit
     * @param array|null $sort
     * @return Criteria
     */
    public function createQueryCriteria(array $filter, array $limit = null, array $sort = null): Criteria
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('eventId', $this->object));
        foreach ($filter['argFilter'] as $by => $expr){
            $criteria = $criteria->andWhere(Criteria::expr()->contains($by, $expr));
        }
        return $criteria->setMaxResults($limit['limit'] ? $limit['limit'] : null)
            ->setFirstResult($limit['offset'] ? $limit['offset'] : null)
            ->orderBy($sort ? $sort : []);
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
            ->map(function (ModelEventAssignment $eventAss) {
                return [
                    'id' => $eventAss->getId(),
                    'alias' => $eventAss->getAlias(),
                    'name' => $eventAss->getName(),
                    'ontologyTerm' => $eventAss->getSboTerm(),
                    'notes' => $eventAss->getNotes(),
                    'formula' => [
                        'latex' => $eventAss->getFormula()->getLatex(),
                        'cmml' => $eventAss->getFormula()->getContentMML()]
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
			throw new Exception('Parent of event assignment must be ' . $className);
		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelEventAssignment::class, 'e')
			->where('e.eventId = :eventId')
			->setParameter('eventId', $this->object->getId());
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
