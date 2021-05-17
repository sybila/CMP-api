<?php

namespace App\Entity\Repositories;

use App\Entity\ModelReaction;
use App\Entity\ModelSpecie;
use App\Entity\ModelReactionItem;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Class ModelReactionItemRepository
 * @package App\Entity\Repositories
 * @author Radoslav Doktor & Marek Havlik
 */
class ModelReactionItemRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	/** @var IdentifiedObject */
	private $parent;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelReactionItem::class);
	}

	protected static function getParentClassName(): array
	{
		return [ModelReaction::class, ModelSpecie::class];
	}

	public function getParent(): IdentifiedObject
	{
		return $this->parent;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelReactionItem::class, $id);
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
            ->map(function (ModelReactionItem $item) {
                return [
                    'id' => $item->getId(),
                    'alias' => $item->getAlias(),
                    'name' => $item->getName(),
                    'ontologyTerm' => $item->getSboTerm(),
                    'notes' => $item->getNotes(),
                    'type' => $item->getType(),
                    'value' => $item->getValue(),
                    'stoichiometry' => $item->getStoichiometry()
                    ];
            })->toArray();
//		$query = $this->buildListQuery($filter)
//			->select('r.id, r.name, r.alias, r.sboTerm, r.notes, r.type, r.value, r.stoichiometry');
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
		$classNames = static::getParentClassName();
		$errorString = '';
		$index = 0;
		foreach ($classNames as $className) {
			if ($object instanceof $className) {
				$this->parent = $object;
				return;
			}
			$index == 0 ?: $errorString .= ' or ';
			$index++;
			$errorString .= $className;
		}
		throw new Exception('Parent of reaction item must be ' . $errorString);
	}

	public function getEntityManager()
	{
		return $this->em;
	}
    public function createQueryCriteria(array $filter, array $limit = null, array $sort = null): Criteria
    {
        if ($this->parent instanceof ModelReaction) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq('reactionId', $this->getParent()));
        }
        if ($this->parent instanceof ModelSpecie) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq('specieId', $this->getParent()));
        }
        foreach ($filter['argFilter'] as $by => $expr){
            $criteria = $criteria->andWhere(Criteria::expr()->contains($by, $expr));
        }
        return $criteria->setMaxResults($limit['limit'] ? $limit['limit'] : null)
            ->setFirstResult($limit['offset'] ? $limit['offset'] : null)
            ->orderBy($sort ? $sort : []);
    }

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = null;
		if ($this->parent instanceof ModelSpecie) {
			$query = $this->em->createQueryBuilder()
				->from(ModelReactionItem::class, 'r')
				->where('r.specieId = :specieId')
				->setParameter('specieId', $this->parent->getId());
		}
		if ($this->parent instanceof ModelReaction) {
			$query = $this->em->createQueryBuilder()
				->from(ModelReactionItem::class, 'r')
				->where('r.reactionId = :reactionId')
				->setParameter('reactionId', $this->parent->getId());
		}
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
