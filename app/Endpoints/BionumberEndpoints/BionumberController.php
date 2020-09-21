<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entity\Bionumber;
use App\Entity\Repositories\BionumberRepository;
use App\Entity\IdentifiedObject;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandra Stanová stanovaalex@mail.muni.cz
 * @property-read BionumberRepository $repository
 * @method Bionumber getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
class BionumberController extends WritableRepositoryController
{


	protected function checkInsertObject(IdentifiedObject $object): void
	{
		/* TODO: Restrictions - discuss */
	}


	protected function createObject(ArgumentParser $body): \App\Entity\IdentifiedObject
	{
		if (!$body->hasKey('name'))
			throw new MissingRequiredKeyException('name');
		if (!$body->hasKey('isValid'))
			throw new MissingRequiredKeyException('isValid');
		if (!$body->hasKey('value'))
			throw new MissingRequiredKeyException('value');
		return new Bionumber;
	}


	protected function getData(IdentifiedObject $bionumber): array
	{
		/** @var Bionumber $bionumber */
		return [
			'id' => $bionumber->getId(),
			'name' => $bionumber->getName(),
			'isValid' => $bionumber->getIsValid(),
			'userId' => $bionumber->getUserId(),
			'organismId' => $bionumber->getOrganismId(),
			'value' => $bionumber->getValue(),
			'link' => $bionumber->getLink(),
			'timeFrom' => $bionumber->getTimeFrom(),
			'timeTo' => $bionumber->getTimeTo(),
			'valueFrom' => $bionumber->getValueFrom(),
			'valueTo' => $bionumber->getValueTo(),
			'valueStep' => $bionumber->getValueStep(),
		];
	}


	protected function getValidator(): \Symfony\Component\Validator\Constraints\Collection
	{
		return new Assert\Collection([
			'organismId' => new Assert\Type(['type' => 'integer']),
			'userId' => new Assert\Type(['type' => 'integer']),
			'name' => new Assert\Type(['type' => 'string']),
			'isValid' => new Assert\Type(['type' => 'integer']),
			'value' => new Assert\Type(['type' => 'float']),
			'link' => new Assert\Type(['type' => 'string']),
			'timeFrom' => new Assert\Type(['type' => 'float']),
			'timeTo' => new Assert\Type(['type' => 'float']),
			'valueFrom' => new Assert\Type(['type' => 'float']),
			'valueTo' => new Assert\Type(['type' => 'float']),
			'valueStep' => new Assert\Type(['type' => 'float']),
		]);
	}


	protected function setData(IdentifiedObject $bionumber, ArgumentParser $data): void
	{
		/** @var Bionumber $bionumber */
		!$data->hasKey('organismId') ?: $bionumber->setOrganismId($data->getInt('organismId'));
		!$data->hasKey('userId') ?: $bionumber->setUserId($data->getInt('userId'));
		!$data->hasKey('name') ?: $bionumber->setName($data->getString('name'));
		!$data->hasKey('isValid') ?: $bionumber->setIsValid($data->getInt('isValid'));
		!$data->hasKey('value') ?: $bionumber->setValue($data->getFloat('value'));
		!$data->hasKey('link') ?: $bionumber->setLink($data->getString('link'));
		!$data->hasKey('timeFrom') ?: $bionumber->setTimeFrom($data->getFloat('timeFrom'));
		!$data->hasKey('timeTo') ?: $bionumber->setTimeTo($data->getFloat('timeTo'));
		!$data->hasKey('valueFrom') ?: $bionumber->setValueFrom($data->getFloat('valueFrom'));
		!$data->hasKey('valueTo') ?: $bionumber->setValueTo($data->getFloat('valueTo'));
		!$data->hasKey('valueStep') ?: $bionumber->setValueStep($data->getFloat('valueStep'));
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}


	protected static function getObjectName(): string
	{
		return 'bionumber';
	}


	protected static function getRepositoryClassName(): string
	{
		return BionumberRepository::class;
	}


	protected static function getAlias(): string
	{
		return 'b';
	}

}
