<?php

namespace App\Controllers;

use App\Entity\BcsNote;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\BcsNoteRepository;
use App\Entity\Repositories\RuleNoteRepository;
use App\Entity\Repositories\RuleRepository;
use App\Entity\RuleNote;
use App\Exceptions\MissingRequiredKeyException;
use App\Exceptions\WrongParentException;
use App\Helpers\ArgumentParser;
use App\Helpers\DateTimeJson;
use Symfony\Component\Validator\Constraints as Assert;

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

	protected function getData(IdentifiedObject $object): array
	{
		/** @var BcsNote $object */
		return [
			'id' => $object->getId(),
			'text' => $object->getText(),
		];
	}

	protected function setData(IdentifiedObject $note, ArgumentParser $body): void
	{
		/** @var BcsNote $note */
		if ($body->hasKey('text'))
			$note->setText($body->getString('text'));

		$note->setUpdated(new DateTimeJson);
	}

	protected static function getObjectName(): string
	{
		return 'rule-note';
	}

	protected static function getParentRepositoryClassName(): string
	{
		return RuleRepository::class;
	}

	protected static function getRepositoryClassName(): string
	{
		return RuleNoteRepository::class;
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new RuleNote;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('rule-id', 'rule');
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'text' => new Assert\NotBlank(),
		]);
	}

	protected function checkInsertObject(IdentifiedObject $note): void
	{
		/** @var BcsNote $note */
		if ($note->getText() == '')
			throw new MissingRequiredKeyException('text');
	}

    protected static function getAlias(): string
    {
        return 'n';
    }

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        // TODO: Implement checkParentValidity() method.
    }
}
