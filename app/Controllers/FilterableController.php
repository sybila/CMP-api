<?php


namespace App\Controllers;


use App\Exceptions\EmptyArraySelection;
use App\Helpers\ArgumentParser;

trait FilterableController
{
    protected static function getFilter(ArgumentParser $args): array
    {
        $filter = [];
        $alias = static::getAlias();
        if ($args->hasKey('filter'))
        {
            foreach ($args->getArray('filter') as $by => $expr)
            {
                $by = $alias . "." . $by;
                $filter[$by] = $expr;
            }
        }
        return $filter;
    }
    abstract protected static function getAlias(): string;

    protected static function validateFilter(int $records, array $filter) {
        if (!$records){
            throw new EmptyArraySelection($filter);
        }
    }
}