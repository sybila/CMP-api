<?php

namespace App\Controllers;

use App\Exceptions\InvalidSortFieldException;
use App\Exceptions\InvalidTypeException;
use App\Helpers\ArgumentParser;

trait ControllerSortable
{
    /**
     * Check & unite format of sort parameters. Returns array, where keys are the attributes that are to be sorted
     * and the values is the manner (asc or desc) of this sorting.
     * @param ArgumentParser $args
     * @return array
     * @throws InvalidSortFieldException
     * @throws InvalidTypeException
     */
	protected static function getSort(ArgumentParser $args): array
	{
		$order = [];
		if ($args->hasKey('sort'))
		{
			foreach ($args->getArray('sort') as $by => $how)
			{
				if (!$how)
					$how = 'ASC';
				else
					$how = strtoupper($how);

				if ($how !== 'ASC' && $how !== 'DESC')
					throw new InvalidSortFieldException('for sort should be \'asc\' or \'desc\'. Otherwise, the query');

				if (!in_array($by, static::getAllowedSort()))
					throw new InvalidSortFieldException($by);

                $order[$by] = $how;
			}
        }
		return $order;
	}
    abstract protected static function getAllowedSort(): array;
}
