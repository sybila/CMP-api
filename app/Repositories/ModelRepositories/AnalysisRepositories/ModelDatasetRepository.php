<?php

namespace App\Entity\Repositories;

use App\Entity\IdentifiedObject;
use App\Entity\Model;
use App\Entity\ModelCompartment;
use App\Entity\ModelDataset;
use App\Entity\ModelVarToDataset;
use App\Exceptions\WrongParentException;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;

/**
 * Class ModelDatasetRepository
 * @package App\Entity\Repositories
 * @author Radoslav Doktor
 */
class ModelDatasetRepository implements IDependentEndpointRepository
{

    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    /**@var Model */
    private $model;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(ModelDataset::class);
    }

    public function getParent(): IdentifiedObject
    {
        return $this->model;
    }

    public function setParent(IdentifiedObject $object): void
    {
        $className = Model::class;
        if (!($object instanceof $className))
            throw new WrongParentException(get_class($object),null,'ModelDataset',null);
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

    public function get(int $id)
    {
        return $this->em->find(ModelDataset::class, $id);
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
            ->map(function (ModelDataset $object) {
                $vars = ['compartments'=> [], 'species' => [], 'parameters' => []];
                $object->getVarsToDataset()->map(function (ModelVarToDataset $var) use (&$vars) {
                    switch ($var->getVarType()) {
                        case 'compartment':
                            /** @var ModelCompartment $cpt */
                            $cpt = $var->getCompartment();
                            array_push($vars['compartments'], [
                                'id' => $cpt->getId(),
                                'alias' => $cpt->getAlias(),
                                'initialValue' => $var->getValue()
                            ]);
                            break;
                        case 'species':
                            $spec = $var->getSpecies();
                            array_push($vars['species'], [
                                'id' => $spec->getId(),
                                'alias' => $spec->getAlias(),
                                'initialValue' => $var->getValue()
                            ]);
                            break;
                        case 'parameter':
                            $par = $var->getParameter();
                            array_push($vars['parameters'], [
                                'id' => $par->getId(),
                                'alias' => $par->getAlias(),
                                'initialValue' => $var->getValue()
                            ]);
                            break;
                    }
                });
                return [
                    "id" => $object->getId(),
                    "name" => $object->getName(),
                    "default" => $object->getIsDefault(),
                    "initialValues" => $vars
                ];
            })->toArray();
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
}