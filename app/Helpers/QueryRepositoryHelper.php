<?php


namespace App\Helpers;


use Doctrine\ORM\QueryBuilder;

trait QueryRepositoryHelper
{
    public static function addFilterPaginationSortDql(QueryBuilder $query, array $filter, array $sort, array $limit ) : QueryBuilder
    {
        if (!empty($filter)) {
            foreach ($filter as $by=>$expr) {
                $query = $query
                    ->andWhere("$by LIKE '%$expr%'");
            }
            //TODO "did you mean {bla bla} (closest similar name, iterate via all models, do similar_text())"
        }
        if ($limit['limit']){
            $query = $query->setMaxResults($limit['limit']);
        }
        if($limit['offset']){
            $query = $query->setFirstResult($limit['offset']);
        }
        if (!empty($sort)) {
            $query = $query->add('orderBy', $sort['fullSortQuery']);
        }
        return $query;
    }

}