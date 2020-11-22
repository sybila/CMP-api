<?php

namespace App\Repositories\Authorization;

use App\Entity\Repositories\IEndpointRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use App\Entity\Authorization\Notification\NotificationLog;

class NotificationLogRepository implements IEndpointRepository
{

    /** @var EntityManager */
    private $em;

    /** @var EntityRepository  */
    private $logRepository;


    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->logRepository = $em->getRepository(NotificationLog::class);
    }

    public function get(int $id)
    {
        return $this->em->find(NotificationLog::class, $id);
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        return $this->logRepository
            ->matching(Criteria::create())
            ->map(function (NotificationLog $notification) {
                return [
                    'id' => $notification->getId(),
                    'who' => $notification->getWhoId(),
                    'what' => json_decode($notification->getWhat()),
                    'when' => json_decode($notification->getWhen())];
            })->toArray();
    }

    /**
     * @param array $filter
     * @param array|null $limit
     * @param array|null $sort
     * @return Criteria
     */
    public function createQueryCriteria(array $filter, array $limit = null, array $sort = null): Criteria
    {
        $criteria = Criteria::create()->where(Criteria::expr()->in('id',
            is_null($filter['accessFilter']['id']) ? [] : $filter['accessFilter']['id']));
        foreach ($filter['argFilter'] as $by => $expr){
            $criteria = $criteria->andWhere(Criteria::expr()->contains($by, $expr));
        }
        return $criteria->setMaxResults($limit['limit'] ? $limit['limit'] : null)
            ->setFirstResult($limit['offset'] ? $limit['offset'] : null)
            ->orderBy($sort ? $sort : []);
    }


    public function getNumResults(array $filter): int
    {
        return $this->logRepository
            ->matching(Criteria::create())
            ->count();
    }
}