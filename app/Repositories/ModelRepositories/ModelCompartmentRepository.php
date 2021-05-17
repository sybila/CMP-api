<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelCompartment;
use App\Entity\IdentifiedObject;
use App\Entity\ModelReaction;
use App\Entity\ModelRule;
use App\Entity\ModelSpecie;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Class ModelCompartmentRepository
 * @package App\Entity\Repositories
 * @author Radoslav Doktor & Marek Havlik
 */
class ModelCompartmentRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	/** @var Model */
	private $model;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelCompartment::class);
	}

	public function get(int $id)
	{
		return $this->em->find(ModelCompartment::class, $id);
	}

    protected static function alias(): string
    {
        return 'c';
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
            ->map(function (ModelCompartment $compartment) {
                return [
                    'id' => $compartment->getId(),
                    'alias' => $compartment->getAlias(),
                    'name' => $compartment->getName(),
//                    'ontologyTerm' => $compartment->getSboTerm(),
//                    'notes' => $compartment->getNotes(),
//                    'spatialDimensions' => $compartment->getSpatialDimensions(),
//                    'size' => $compartment->getSize(),
//                    'constant' => $compartment->getConstant(),
                    'species' => $compartment->getSpecies()->map(function (ModelSpecie $specie) {
                        return ['id' => $specie->getId(), 'name' => $specie->getName()];
                    })->toArray(),
//                    'rules' => $compartment->getRules()->map(function (ModelRule $rule) {
//                        return ['id' => $rule->getId(), 'equation' => [
//                            'latex' => is_null($rule->getExpression()) ? '' :$rule->getExpression()->getLatex(),
//                            'cmml' => is_null($rule->getExpression()) ? '' : $rule->getExpression()->getContentMML()]];
//                    })->toArray(),
                ];
            })->toArray();
//		$query = $this->buildListQuery($filter)
//			->select('c.id, c.name, c.alias, c.sboTerm, c.notes, c.spatialDimensions, c.size, c.constant');
//        $query = $this->addPagingDql($query, $limit);
//        $query = $this->addSortDql($query, $sort);
//        return $query->getQuery()->getArrayResult();
	}

    public function createQueryCriteria(array $filter, array $limit = null, array $sort = null): Criteria
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('model', $this->getParent()));
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
			->from(ModelCompartment::class, 'c')
			->where('c.model = :modelId')
			->setParameter('modelId', $this->model->getId());
        $query = $this->addFilterDql($query, $filter);
		return $query;
	}

    public function getParent(): IdentifiedObject
    {
        return $this->model;
    }

    /**
     * @param IdentifiedObject $object
     * @throws Exception
     */
    public function setParent(IdentifiedObject $object): void
    {
        $className = Model::class;
        if (!($object instanceof $className))
            throw new Exception('Parent of compartment must be ' . $className);
        $this->model = $object;
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
