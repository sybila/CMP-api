<?php

namespace App\Controllers;

use App\Entity\BcsNote;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\BcsNoteRepository;
use App\Entity\Repositories\RuleNoteRepository;
use App\Entity\Repositories\RuleRepository;
use App\Entity\Repositories\RuleRepositoryImpl;
use App\Entity\RuleNote;
use App\Exceptions\MalformedInputException;
use App\Helpers\ArgumentParser;

/**
 * @property-read BcsNoteRepository $repository
 * @method BcsNote getObject(int $id)
 * @property-read RuleRepository $parentRepository
 */
class RuleNoteController extends ParentedRepositoryController
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

		$entity->setUpdated(new \DateTime);
	}

	protected static function getObjectName(): string
	{
		return 'rule-note';
	}

	protected static function getParentRepositoryClassName(): string
	{
		return RuleRepositoryImpl::class;
	}

	protected static function getRepositoryClassName(): string
	{
		return RuleNoteRepository::class;
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new RuleNote;
	}

	protected function getParentObjectInfo(): array
	{
		return ['rule-id', 'rule'];
	}
}
