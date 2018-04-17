<?php

namespace App\Controllers;

use App\Exceptions\InvalidSortFieldException;
use App\Helpers\ArgumentParser;

trait SortableController
{
	abstract protected static function getAllowedSort(): array;

	protected static function getSort(ArgumentParser $args): ?array
	{
		$what = '';
		$by = '';

//		foreach ($args['sort'] as $by => $how)
//		{
//			if (!$how)
//				$how = 'ASC';
//			else
//				$how = strtoupper($how);
//
//			if ($how !== 'ASC' && $how !== 'DESC')
//				throw new InvalidSortFieldException('ascdesc');
//
//			if (!in_array($by, self::getAllowedSort()))
//				throw new InvalidSortFieldException($by);
//
//			$order[$by] = $how;
//		}
		if ($args->hasKey('sort'))
		{
			$what = $args->getString('sort');
			if (!in_array($what, self::getAllowedSort()))
				throw new InvalidSortFieldException($what);

			if ($args->hasKey('sortDirection'))
			{
				$by = strtoupper($args->getString('sortDirection'));
				if ($by !== 'ASC' && $by !== 'DESC')
					throw new InvalidSortFieldException('ascdesc');
			}
			else
				$by = 'ASC';
		}

		return $what ? [$what => $by] : null;
	}
}
