<?php

namespace App\Controllers;

use App\Exceptions\InvalidArgumentException;
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;

trait PageableController
{
	protected static function getPaginationData(ArgumentParser $args, int $resultCount): array
	{
		Validators::validate($args, 'pagination', 'invalid pagination data');

		if ($args->hasKey('take'))
		{
			$offset = 0;
			if ($args->hasKey('skip'))
				$offset = $args->getInt('skip');

			return ['limit' => $args->getInt('take'), 'offset' => $offset, 'pages' => 0];
		}
		else
		{
			$perPage = 0;
			if ($args->hasKey('perPage'))
				$perPage = $args->getInt('perPage');

			$page = 0;
			if ($args->hasKey('page'))
				$page = $args->getInt('page') - 1;

			if ($page * $perPage > $resultCount || $page < 0)
				throw new InvalidArgumentException('page', $page + 1, 'page out of range');

			return ['limit' => $perPage, 'offset' => $page * $perPage, 'pages' => $perPage ? ceil($resultCount / $perPage) : 1];
		}
	}
}
