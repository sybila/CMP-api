<?php

namespace App\Controllers;

use App\Entity\{
	Authorization\UserType,
	IdentifiedObject
};
use App\Repositories\{
	Authorization\UserTypeRepository
};

final class UserTypeController extends RepositoryController
{

	protected function getData(IdentifiedObject $userType): array
	{
		/** @var UserType $userType */
		return [
			'id' => $userType->getId(),
			'tier' => $userType->getTier(),
			'name' => $userType->getName()
		];
	}


	protected static function getAllowedSort(): array
	{
		return ['tier'];
	}


	protected static function getObjectName(): string
	{
		return 'userType';
	}


	protected static function getRepositoryClassName(): string
	{
		return UserTypeRepository::class;
	}

}
