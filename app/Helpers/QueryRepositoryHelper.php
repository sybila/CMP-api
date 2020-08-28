<?php


namespace App\Helpers;


use App\Entity\Authorization\User;
use App\Entity\Authorization\UserGroup;
use App\Entity\Experiment;
use App\Entity\Model;
use Doctrine\ORM\QueryBuilder;

trait QueryRepositoryHelper
{
    public static function addPaginationSortDql(QueryBuilder $query, array $sort, array $limit) : QueryBuilder
    {
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

    public static function addFilterDql(QueryBuilder $query, array $filter) : QueryBuilder
    {
        if (!empty($filter['accessFilter'])){
            $query = static::addAccesibleDql($query, $filter['accessFilter']);
        }
        if (!empty($filter['argFilter'])) {
            foreach ($filter['argFilter'] as $by=>$expr) {
                $query = $query
                    ->andWhere("$by LIKE '%$expr%'");
            }
        }
        return $query;
    }

    public static function addAccesibleDql(QueryBuilder $query, ?array $accessFilter) : QueryBuilder
    {
        if(in_array($query->getRootEntities()[0],
            [Model::class, Experiment::class, UserGroup::class, User::class]) != false){
            foreach ($accessFilter as $owner=>$groupId) {
                $query = $query
                    ->orWhere("$groupId = $owner");
            }
        }
        return $query;
    }

}