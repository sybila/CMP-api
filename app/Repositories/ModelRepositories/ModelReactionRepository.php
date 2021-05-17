<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelReaction;
use App\Entity\IdentifiedObject;
use App\Entity\ModelReactionItem;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Class ModelReactionRepository
 * @package App\Entity\Repositories
 * @author Radoslav Doktor & Marek Havlik
 */
class ModelReactionRepository implements IDependentEndpointRepository
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
		$this->repository = $em->getRepository(ModelReaction::class);
	}

	protected static function getParentClassName(): string
	{
		return Model::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelReaction::class, $id);
	}

	public function getParent(): IdentifiedObject
	{
		return $this->model;
	}

    protected static function alias(): string
    {
        return 'r';
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
            ->map(function (ModelReaction $reaction) {
                return [
                    'id' => $reaction->getId(),
                    'name' => $reaction->getName(),
                    'ontologyTerm' => $reaction->getSboTerm(),
                    'isReversible' => $reaction->getIsReversible(),
                    'rate' => [
                        'latex' => is_null($reaction->getRate()) ? '' : $reaction->getRate()->getLatex(),
                        'cmml' => is_null($reaction->getRate()) ? '' : $reaction->getRate()->getContentMML()],
                    'reactionItems' => $reaction->getReactionItems()->map(function (ModelReactionItem $reactionItem) {
                        return ['id' => $reactionItem->getId(),
                            'name' => $reactionItem->getName(),
                            'alias' => $reactionItem->getAlias(),
                            'stoichiometry' => $reactionItem->getStoichiometry(),
                            'type' => $reactionItem->getType()];
                    })->toArray()
                ];
            })->toArray();
//
//		$query = $this->buildListQuery($filter)
//			->select('r.id, r.name, r.sbmlId, r.sboTerm, r.notes, r.isReversible, r.rate');
//        $query = $this->addPagingDql($query, $limit);
//        $query = $this->addSortDql($query, $sort);
//		return $query->getQuery()->getArrayResult();
	}

    /**
     * @param IdentifiedObject $object
     * @throws Exception
     */
	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new Exception('Parent of reaction must be ' . $className);
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
			->from(ModelReaction::class, 'r')
			->where('r.modelId = :modelId')
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
