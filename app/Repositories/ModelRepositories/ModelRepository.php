<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ModelRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\ModelRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Model::class);
	}

	public function get(int $id)
	{
		//dump($this->em->find(Model::class, $id));exit;
		return $this->em->find(Model::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(m)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('m.id, m.name, m.sbmlId, m.sboTerm, m.notes, m.annotation, m.userId, m.approvedId, m.status');
		if ($limit['limit']){
		    $query = $query->setMaxResults($limit['limit']);
        }
		if($limit['offset']){
		    $query = $query->setFirstResult($limit['offset']);
        }
        if (!empty($sort)) {
            $sortBy = '';
            foreach ($sort as $by=>$how) {
                if ($sortBy != null) {
                    $sortBy .= ', ';
                }
                $sortBy .= "m.{$by} {$how}";
            }
            $query = $query->add('orderBy', $sortBy);
        }
		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Model::class, 'm');
		if (!empty($filter)) {
		    foreach ($filter as $by=>$expr) {
                $query = $query
                    ->andWhere("m.$by LIKE '%$expr%'");
		    }
		    //TODO "did you mean {bla bla} (closest similar name, iterate via all models, do similar_text())"
		}
		return $query;
	}

}
