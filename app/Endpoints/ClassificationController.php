<?php

namespace App\Controllers;

use App\Entity\Classification;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\ClassificationRepository;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\MalformedInputException;
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;

/**
 * @property-read ClassificationRepository $repository
 * @method Classification getObject(int $id)
 */
final class ClassificationController extends WritableRepositoryController
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
		return ClassificationRepository::class;
	}

	protected static function getObjectName(): string
	{
		return 'classification';
	}

	/**
	 * @param Classification $classification
	 * @param ArgumentParser $body
	 * @param bool           $insert
	 */
	protected function setData($classification, ArgumentParser $body, bool $insert): void
	{
		Validators::validate($body, 'classification', 'invalid data for classification');

		if ($body->hasKey('name'))
			$classification->setName($body->getString('name'));

		if ($insert && $classification->getName() == '')
			throw new MalformedInputException('Input doesn\'t contain all required fields');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('type'))
			throw new InvalidArgumentException('type', null);

		$cls = array_search($type = $body->getString('type'), Classification::$classToType, true);
		if (!$cls)
			throw new InvalidArgumentException('type', $type);

		return new $cls;
	}
}
