<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Entity\Organism;
use App\Entity\Repositories\OrganismRepository;
use App\Exceptions\MalformedInputException;
use App\Helpers\ArgumentParser;
use App\Helpers\Validators;

/**
 * @property-read OrganismRepository $repository
 * @method Organism getObject(int $id)
 */
final class OrganismController extends WritableRepositoryController
{
	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'code'];
	}

	/**
	 * @param Organism $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'name' => $entity->getName(),
			'code' => $entity->getCode(),
		];
	}

	protected static function getRepositoryClassName(): string
	{
		return OrganismRepository::class;
	}

	protected static function getObjectName(): string
	{
		return 'organism';
	}

	/**
	 * @param Organism $organism
	 * @param ArgumentParser $body
	 * @param bool           $insert
	 */
	protected function setData($organism, ArgumentParser $body, bool $insert): void
	{
		Validators::validate($body, 'organism', 'invalid data for organism');

		if ($body->hasKey('name'))
			$organism->setName($body->getString('name'));
		if ($body->hasKey('code'))
			$organism->setCode($body->getString('code'));

		if ($insert && ($organism->getName() == '' || $organism->getCode() == ''))
			throw new MalformedInputException('Input doesn\'t contain all required fields');
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new Organism;
	}
}
