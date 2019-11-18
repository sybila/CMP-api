<?php

namespace App\Controllers;

use App\Entity\{
	Authorization\UserGroupRole,
	IdentifiedObject
};
use App\Repositories\{
	Authorization\UserGroupRoleRepository
};

final class UserGroupRoleController extends RepositoryController
{


	protected function getData(IdentifiedObject $userGroupRole): array
	{
		/** @var UserGroupRole $userGroupRole */
		return [
			'id' => $userGroupRole->getId(),
			'tier' => $userGroupRole->getTier(),
			'name' => $userGroupRole->getName()
		];
	}


	protected static function getAllowedSort(): array
	{
		return ['tier'];
	}


	protected static function getObjectName(): string
	{
		return 'userGroupRole';
	}


	protected static function getRepositoryClassName(): string
	{
		return UserGroupRoleRepository::class;
	}

}
