<?php

namespace App\Controllers;

use App\Entity\Repositories\ExperimentRepository;
use App\Exceptions\InvalidArgumentException;
use App\Helpers\ArgumentParser;
use PhpParser\Error;
use Symfony\Component\Validator\Constraints as Assert;

trait PageableController
{
	use ValidatedController;

	protected static function getPaginationData(ArgumentParser $args, int $resultCount): array
	{
		self::validate($args, self::getPaginationValidator());
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

			if ($page * $perPage - 1 > $resultCount || $page < 0)
				throw new InvalidArgumentException('page', $page + 1, 'page out of range');

			return ['limit' => $perPage, 'offset' => $page * $perPage, 'pages' => $perPage ? ceil($resultCount / $perPage) : 1];
		}
	}

    /**
     * @return array with attr 'attr' that shows on what attribute can the paging be performed on
     * and with 'parent' that defines the parent attribute in array while calling detail (readIdentified).
     * TODO need to solve paging on deeper lvl (maybe useless?)
     */
    protected static function getAllowedPagingVar(): ?array
    {
        $pagingConditions = null;
        switch (get_called_class()){
            case VariablesValuesController::class:
                $pagingConditions = ['attr' => 'values', 'parent' => 'variables'];
                break;
            default:
                break;
        }
        return $pagingConditions;
    }

    /**
     * If paging arguments are missing and detail for the class is not allowed this returns $data_response
     * @param ArgumentParser $args
     * @param array $data_response
     * @return array
     */
	protected static function getPaginationOnDetail(ArgumentParser $args, array $data_response): array
    {
        $paging = static::getAllowedPagingVar();
        if ($args->hasKey('perPage') && $args->hasKey('page') && !is_null($paging)){
            $i = 0;
            $numResult = 0;
            foreach ($data_response[$paging['parent']] as $p_var) {
                $paginated_data = $p_var[$paging['attr']];
                $numResult = count($paginated_data) > $numResult ? count($paginated_data) :  $numResult;
                $data_response[$paging['parent']][$i][$paging['attr']] = array_slice($paginated_data,
                    ($args['page'] - 1) * $args['perPage'], $args['perPage']);
                $i = $i + 1;
            }
            $data[] = $data_response;
            $data['maxCount'] = $numResult;

            if(($args['perPage'] ? ceil($numResult / $args['perPage']) : 1)<$args['page']){
                throw new InvalidArgumentException('page', $args['page'], 'page out of range');
            }
            return $data;
        }
        return $data_response;
    }

	protected static function getPaginationValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'page' => new Assert\Range(['min' => 1]),
			'perPage' => new Assert\Range(['min' => 0]),
			'skip' => new Assert\Range(['min' => 0]),
			'take' => new Assert\Range(['min' => 1]),
		]);
	}
}
