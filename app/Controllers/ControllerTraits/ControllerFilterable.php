<?php


namespace App\Controllers;


use App\Exceptions\EmptyArraySelection;
use App\Exceptions\InvalidTypeException;
use App\Helpers\ArgumentParser;

/**
 * Trait ControllerFilterable
 * @package App\Controllers
 * @author Jakub Hrabec
 */
trait ControllerFilterable
{
    /**
     * Returns array prepared by argument parser
     * @param ArgumentParser $args
     * @return array
     * @throws InvalidTypeException
     */
    protected static function getFilter(ArgumentParser $args): array
    {
        if ($args->hasKey('filter')) {
            return $args->getArray('filter');
        }
        return [];
    }

    /**
     * If the there is none record that matches the defined filter throw an exception.
     * @param int $records
     * @param array $filter
     * @throws EmptyArraySelection
     */
    protected static function validateFilter(int $records, array $filter): void
    {
        if (!$records){
            throw new EmptyArraySelection($filter);
        }
    }
}