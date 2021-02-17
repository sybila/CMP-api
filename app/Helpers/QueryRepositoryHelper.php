<?php


namespace App\Helpers;


use App\Entity\Authorization\User;
use App\Entity\Authorization\UserGroup;
use App\Entity\Experiment;
use App\Entity\Model;
use App\Exceptions\InvalidSortFieldException;
use Doctrine\ORM\QueryBuilder;

trait QueryRepositoryHelper
{
    public static function addPagingDql(QueryBuilder $query, array $limit) : QueryBuilder
    {
        if ($limit['limit']){
            $query = $query->setMaxResults($limit['limit']);
        }
        if($limit['offset']){
            $query = $query->setFirstResult($limit['offset']);
        }
        return $query;
    }

    public static function addSortDql(QueryBuilder $query, array $sort) : QueryBuilder
    {
        $alias = static::alias();
        $fullSortQuery = '';
        if (!empty($sort)) {
            foreach ($sort as $by => $how) {
                if ($fullSortQuery != null){
                    $fullSortQuery .= ', ';
                }
                $fullSortQuery .= $alias . ".{$by} {$how}";
            }
            $query = $query->add('orderBy', [$fullSortQuery]);
        }
        return $query;
    }

    public static function addFilterDql(QueryBuilder $query, array $filter) : QueryBuilder
    {
        if (!empty($filter['accessFilter'])){
            $query = static::addAccessibleDql($query, $filter['accessFilter']);
        }
        $alias = static::alias();
        if (!empty($filter['argFilter'])) {
            foreach ($filter['argFilter'] as $by=>$expr) {
                $query = $query
                    ->andWhere("$alias.$by LIKE '%$expr%'");
            }
        }
        return $query;
    }

    public static function addAccessibleDql(QueryBuilder $query, ?array $accessFilter) : QueryBuilder
    {
        if(in_array($query->getRootEntities()[0],
            [Model::class, Experiment::class, UserGroup::class, User::class]) != false){
            foreach ($accessFilter as $attr_id=>$attr) {
                $query = $query
                    ->orWhere("$attr = $attr_id");
            }
        }
        return $query;
    }

}