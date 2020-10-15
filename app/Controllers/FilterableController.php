<?php


namespace App\Controllers;


use App\Exceptions\EmptyArraySelection;
use App\Exceptions\InvalidTypeException;
use App\Helpers\ArgumentParser;

trait FilterableController
{
    /**
     * Returns array prepared for DQL query (WHERE x LIKE y).
     * Array has the entity (table) alias joined with an attribute as keys (x in mentioned query),
     * and the values are the 'y' expressions, that are supposed to be matched with the table records.
     * @param ArgumentParser $args
     * @return array
     * @throws InvalidTypeException
     */
    protected static function getFilter(ArgumentParser $args): array
    {
        static::getRepositoryClassName();
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

    /**
     * If the there is none record that matches the defined filter throw an exception.
     * @param int $records
     * @param array $filter
     * @throws EmptyArraySelection
     */
    protected static function validateFilter(int $records, array $filter) {
        if (!$records){
            throw new EmptyArraySelection($filter);
        }
    }
}