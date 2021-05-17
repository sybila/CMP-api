<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelFunctionDefinition;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Class ModelFunctionDefinitionRepository
 * @package App\Entity\Repositories
 * @author Radoslav Doktor & Marek Havlik
 */
class ModelFunctionDefinitionRepository implements IDependentEndpointRepository
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
		$this->repository = $em->getRepository(ModelFunctionDefinition::class);
	}

	protected static function getParentClassName(): string
	{
		return Model::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelFunctionDefinition::class, $id);
	}

    protected static function alias(): string
    {
        return 'f';
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
            ->map(function (ModelFunctionDefinition $fnDef) {
                return [
                    'id' => $fnDef->getId(),
                    'alias' => $fnDef->getAlias(),
                    'name' => $fnDef->getName(),
                    'ontologyTerm' => $fnDef->getSboTerm(),
                    'notes' => $fnDef->getNotes(),
                    'expression' => [
                        'latex' => is_null($fnDef->getExpression()) ? '' : $fnDef->getExpression()->getLatex(),
                        'cmml' => is_null($fnDef->getExpression()) ? '' : $fnDef->getExpression()->getContentMML()],
                ];
            })->toArray();
//		$query = $this->buildListQuery($filter)
//			->select('f.id, f.name, f.alias, f.sboTerm, f.notes, f.expression');
//        $query = $this->addPagingDql($query, $limit);
//        $query = $this->addSortDql($query, $sort);
//		return $query->getQuery()->getArrayResult();
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
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new Exception('Parent of initial assignment must be ' . $className);
		$this->model = $object;
	}

    /**
     * @param array $filter
     * @param array|null $limit
     * @param array|null $sort
     * @return Criteria
     */
    public function createQueryCriteria(array $filter, array $limit = null, array $sort = null): Criteria
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('modelId', $this->getParent()));
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
			->from(ModelFunctionDefinition::class, 'f')
			->where('f.modelId = :modelId')
			->setParameter('modelId', $this->model->getId());
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
