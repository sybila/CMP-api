<?php

namespace App\Controllers;

use App\Entity\Classification;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\ClassificationRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

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

	protected function getData(IdentifiedObject $object): array
	{
		/** @var Classification $object */
		return [
			'id' => $object->getId(),
			'name' => $object->getName(),
			'type' => Classification::$classToType[get_class($object)],
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

	protected function setData(IdentifiedObject $classification, ArgumentParser $body): void
	{
		/** @var Classification $classification */
		if ($body->hasKey('name'))
			$classification->setName($body->getString('name'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('type'))
			throw new MissingRequiredKeyException('type');

		$cls = array_search($body['type'], Classification::$classToType, true);
		return new $cls;
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'type' => new Assert\Choice(array_values(Classification::$classToType)),
			'name' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected function checkInsertObject(IdentifiedObject $classification): void
	{
		/** @var Classification $classification */
		if ($classification->getName() == '')
			throw new MissingRequiredKeyException('name');
	}

    protected static function getAlias(): string
    {
        return 'c';
    }
}
