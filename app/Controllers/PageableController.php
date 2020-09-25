<?php

namespace App\Controllers;

use App\Exceptions\InvalidArgumentException;
use App\Exceptions\InvalidTypeException;
use App\Exceptions\MalformedInputException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

trait PageableController
{
	use ValidatedController;

    /**
     * Parses the relevant settings for the pagination from the URL arguments,
     * returns them in associative array; keys are 'limit', 'offset', 'pages'.
     * @param ArgumentParser $args
     * @param int $resultCount
     * @return array
     * @throws InvalidArgumentException|InvalidTypeException|MalformedInputException
     */
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
     * @param array $dataResponse
     * @return array
     * @throws InvalidArgumentException
     * @throws MalformedInputException
     */
	protected static function getPaginationOnDetail(ArgumentParser $args, array $dataResponse): array
    {
        self::validate($args, self::getPaginationValidator());
        $paging = static::getAllowedPagingVar();
        if ($args->hasKey('perPage') && $args->hasKey('page') && !is_null($paging)){
            $i = 0;
            $numResult = 0;
            foreach ($dataResponse[$paging['parent']] as $parent) {
                $paginatedData = $parent[$paging['attr']];
                $numResult = count($paginatedData) > $numResult ? count($paginatedData) :  $numResult;
                $dataResponse[$paging['parent']][$i][$paging['attr']] = array_slice($paginatedData,
                    ($args['page'] - 1) * $args['perPage'], $args['perPage']);
                $i = $i + 1;
            }
            $data[] = $dataResponse;
            $data['maxCount'] = $numResult;

            if(($args['perPage'] ? ceil($numResult / $args['perPage']) : 1)<$args['page']){
                throw new InvalidArgumentException('page', $args['page'], 'page out of range');
            }
            return $data;
        }
        return $dataResponse;
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
