<?php


namespace App\Entity\Repositories;


use App\Entity\AnnotationSource;
use App\Entity\IdentifiedObject;
use Doctrine\ORM\EntityManager;

class AnnotationSourceRepository implements IDependentEndpointRepository
{
    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    /** @var IdentifiedObject */
    private $parent;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(AnnotationSource::class);
    }
    public function get(int $id)
    {
        return $this->em->find(AnnotationSource::class, $id);
    }

    //----  annotations endpoint do not support LIST operation ----//
    public function getNumResults(array $filter): int
    {
        return 0;
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        return [];
    }
    //--------//

    public function getParent(): IdentifiedObject
    {
        return $this->parent;
    }

    public function setParent(IdentifiedObject $object): void
    {
        $this->parent = $object;
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