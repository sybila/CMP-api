<?php

declare(strict_types=1);

namespace App\Entity\Repositories;

use App\Entity\Bioquantity;
use App\Helpers\QueryRepositoryHelper;
use ArrayIterator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Traversable;

/**
 * @author Alexandra Stanová stanovaalex@mail.muni.cz
 */
class BioquantityRepository implements IEndpointRepository
{
	use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Bioquantity::class);
	}

	public function get(int $id)
	{
		return $this->em->find(Bioquantity::class, $id);
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
        $entities =  $this->repository
            ->matching($this->createQueryCriteria($filter, $limit, $sort));
        if (key_exists('organism', $sort)) {
            $entities = $this->sortByOrganismName($entities->getIterator());
        }
        $entities = $entities->map(function (Bioquantity $bq) {
            return [
                'id' => $bq->getId(),
                'name' => $bq->getName(),
                'user' => $bq->getUserId(),
                'organism' => $bq->getOrganism()->getName(),
                'isValid' => $bq->getIsValid(),
                'value' => $bq->getValue(),
                'link' => $bq->getLink(),
                'timeFrom' => $bq->getTimeFrom(),
                'timeTo' => $bq->getTimeTo(),
                'valueFrom' => $bq->getValueFrom(),
                'valueTo' => $bq->getValueTo(),
                'valueStep' => $bq->getValueStep()
            ];
        })->partition(function ($key, $bqObj) use ($filter){
            $logicSum = true;
            foreach ($filter['argFilter'] as $by => $expr) {
                $logicSum = $logicSum && (bool) stristr($bqObj[$by], $expr);
            }
            return $logicSum;
        });
        return $entities[0]->slice($limit['offset'], $limit['limit'] ? $limit['limit'] : null );
	}

	public function sortByOrganismName (Traversable $iterator){
        $iterator->uasort(function (Bioquantity $a, Bioquantity $b) {
            return $a->getOrganism()->getName() <=> $b->getOrganism()->getName();
        });
        return new ArrayCollection(iterator_to_array($iterator));
    }


	public function getNumResults(array $filter): int
	{
	    return count($this->getList($filter,[],[]));
	}

    /**
     * @param array $filter
     * @param array|null $limit
     * @param array|null $sort
     * @return Criteria
     */
    public function createQueryCriteria(array $filter, array $limit = null, array $sort = null): Criteria
    {
        return $criteria = Criteria::create();
    }
}
