<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelParameter;
use App\Entity\ModelReaction;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;

class ModelParameterRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	/** @var Model */
	private $parent;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelParameter::class);
	}

	public function getBySbmlId(string $sbmlId)
	{
		return $this->repository->findOneBy(['alias' => $sbmlId]);
	}

	protected static function getParentClassName(): array
	{
		return [Model::class, ModelReaction::class];
	}

	public function getParent(): IdentifiedObject
	{
		return $this->parent;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelParameter::class, $id);
	}

    protected static function alias(): string
    {
        return 'p';
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
            ->map(function (ModelParameter $par) {
                return [
                    'id' => $par->getId(),
                    'alias' => $par->getAlias(),
                    'name' => $par->getName(),
                    'value' => $par->getDefaultValue(),
                    'constant' => $par->getConstant() ? 'true' : 'false',
                    'ontologyTerm' => $par->getSboTerm(),
                ];
            })->toArray();
//		$query = $this->buildListQuery($filter)
//			->select('p.id, p.name, p.alias, p.sboTerm, p.notes, p.value, p.isConstant');
//        $query = $this->addPagingDql($query, $limit);
//        $query = $this->addSortDql($query, $sort);
//		return $query->getQuery()->getArrayResult();
	}

    /**
     * @param array $filter
     * @param array|null $limit
     * @param array|null $sort
     * @return Criteria
     */
    public function createQueryCriteria(array $filter, array $limit = null, array $sort = null): Criteria
    {
        $criteria = Criteria::create();
        if ($this->parent instanceof Model) {
            $criteria->where(Criteria::expr()->eq('model', $this->getParent()));
        }
        if ($this->parent instanceof ModelReaction) {
            $criteria->where(Criteria::expr()->eq('reactionId', $this->getParent()));
        }
        foreach ($filter['argFilter'] as $by => $expr){
            $criteria = $criteria->andWhere(Criteria::expr()->contains($by, $expr));
        }
        return $criteria->setMaxResults($limit['limit'] ? $limit['limit'] : null)
            ->setFirstResult($limit['offset'] ? $limit['offset'] : null)
            ->orderBy($sort ? $sort : []);
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
		foreach ($classNames as $clsName) {
			if ($object instanceof $clsName) {
				$this->parent = $object;
				return;
			}
			$index == 0 ?: $errorString .= ' or ';
			$index++;
			$errorString .= $clsName;
		}
		throw new Exception('Parent of parameter must be ' . $errorString);
	}

	public function getEntityManager()
	{
		return $this->em;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = null;
		if ($this->parent instanceof Model) {
			$query = $this->em->createQueryBuilder()
				->from(ModelParameter::class, 'p')
				->where('p.modelId = :modelId') // AND p.reactionId IS NULL')
				->setParameter('modelId', $this->parent->getId());
		}
		if ($this->parent instanceof ModelReaction) {
			$query = $this->em->createQueryBuilder()
				->from(ModelParameter::class, 'p')
				->where('p.reactionId = :reactionId')
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
