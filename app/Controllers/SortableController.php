<?php

namespace App\Controllers;

use App\Exceptions\InvalidSortFieldException;
use App\Helpers\ArgumentParser;

trait SortableController
{
	abstract protected static function getAllowedSort(): array;
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
					throw new InvalidSortFieldException('ascdesc');

				if (!in_array($by, self::getAllowedSort()))
					throw new InvalidSortFieldException($by);

				$order[$by] = $how;
			}
		}

		return $order;
	}
}
