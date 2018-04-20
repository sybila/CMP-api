<?php

namespace App\Controllers;

use App\Entity\Classification;
use App\Entity\EntityClassification;
use App\Entity\Repositories\ClassificationRepository;
use App\Entity\Repositories\ClassificationRepositoryImpl;
use App\Entity\RuleClassification;
use App\Exceptions\
{
	ApiException, InternalErrorException, InvalidArgumentException, NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Container;
use Slim\Http\{Request, Response};

/**
 * @property-read ClassificationRepository $repository
 * @method Classification getObject(int $id)
 */
final class ClassificationController extends RepositoryController
{
	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getFilter(ArgumentParser $args): array
	{
		if ($args->hasKey('type'))
			return ['type' => $args->getString('type')];

		return [];
	}

	/**
	 * @param Classification $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'name' => $entity->getName(),
			'type' => Classification::$classToType[get_class($entity)],
		];
	}

	protected static function getRepositoryClassName(): string
	{
		return ClassificationRepositoryImpl::class;
	}

	protected static function getObjectName(): string
	{
		return 'classification';
	}
}
