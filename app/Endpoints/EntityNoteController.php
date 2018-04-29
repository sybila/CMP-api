<?php

namespace App\Controllers;

use App\Entity\BcsNote;
use App\Entity\EntityNote;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\BcsNoteRepository;
use App\Entity\Repositories\EntityRepository;
use App\Entity\Repositories\EntityNoteRepository;
use App\Exceptions\MalformedInputException;
use App\Helpers\ArgumentParser;
use App\Helpers\DateTimeJson;

/**
 * @property-read BcsNoteRepository $repository
 * @method BcsNote getObject(int $id)
 * @property-read EntityRepository $parentRepository
 */
class EntityNoteController extends ParentedRepositoryController
{
	protected static function getAllowedSort(): array
	{
		return ['id'];
	}

	/**
	 * @param BcsNote $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'text' => $entity->getText(),
		];
	}

	/**
	 * @param BcsNote        $entity
	 * @param ArgumentParser $body
	 * @param bool           $insert
	 */
	protected function setData($entity, ArgumentParser $body, bool $insert): void
	{
		if ($body->hasKey('text'))
			$entity->setText($body->getString('text'));

		if ($insert && !$body->hasKey('text'))
			throw new MalformedInputException('Input doesn\'t contain all required fields');

		$entity->setUpdated(new DateTimeJson);
	}

	protected static function getObjectName(): string
	{
		return 'entity-note';
	}

	protected static function getParentRepositoryClassName(): string
	{
		return EntityRepository::class;
	}

	protected static function getRepositoryClassName(): string
	{
		return EntityNoteRepository::class;
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new EntityNote;
	}

	protected function getParentObjectInfo(): array
	{
		return ['entity-id', 'entity'];
	}
}
