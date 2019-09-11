<?php

namespace App\Controllers;

use App\Entity\{
	Authorization\User,
	Authorization\UserGroup,
	Authorization\UserGroupToUser,
	IdentifiedObject
};
use App\Entity\Repositories\IEndpointRepository;
use App\Repositories\Authorization\UserGroupRepository;
use App\Exceptions\{
	DependentResourcesBoundException,
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request,
	Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read UserGroupRepository $repository
 * @method Model getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class UserGroupController extends WritableRepositoryController
{

	/** @var UserGroupRepository */
	private $userGroupRepository;


	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->userGroupRepository = $c->get(UserGroupRepository::class);
	}


	protected static function getAllowedSort(): array
	{
		return ['id, name'];
	}


	protected function getData(IdentifiedObject $userGroup): array
	{
		/** @var UserGroup $userGroup */
		return [
			'id' => $userGroup->getId(),
			'name' => $userGroup->getName(),
			'users' => $userGroup->getUsers()->map(function (UserGroupToUser $userLink) {
					$user = $userLink->getUserId();
					return ['role' => $userLink->getRoleId(), 'id' => $user->getId(), 'name' => $user->getName(), 'surname' => $user->getSurname()];
				})->toArray(),
		];
	}


	protected function setData(IdentifiedObject $userGroup, ArgumentParser $body): void
	{
		/** @var UserGroup $userGroup */
	}


	protected function createObject(ArgumentParser $body): IdentifiedObject
	{

	}


	protected function checkInsertObject(IdentifiedObject $userGroup): void
	{

	}


	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{

	}


	protected function getValidator(): Assert\Collection
	{

	}


	protected static function getObjectName(): string
	{
		return 'userGroup';
	}


	protected static function getRepositoryClassName(): string
	{
		return UserGroupRepository::Class;
	}

}
