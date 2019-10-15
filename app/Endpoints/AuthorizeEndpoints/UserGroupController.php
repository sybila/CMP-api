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
			'type' => (int) $userGroup->getType(),
			'description' => $userGroup->getDescription(),
			'users' => $userGroup->getUsers()->map(function (UserGroupToUser $userLink) {
					$user = $userLink->getUserId();
					return ['role' => $userLink->getRoleId(), 'id' => $user->getId(), 'name' => $user->getName(), 'surname' => $user->getSurname()];
				})->toArray(),
		];
	}


	protected function setData(IdentifiedObject $userGroup, ArgumentParser $data): void
	{
		/** @var UserGroup $userGroup */
		!$body->hasKey('name') ?: $model->setApprovedId($body->getString('name'));
		!$body->hasKey('type') ?: $model->setApprovedId($body->getString('type'));
		!$body->hasKey('description') ?: $model->setApprovedId($body->getString('description'));
	}


	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		$this->verifyMandatoryArguments(['name', 'type', 'description'], $body);
		return new UserGroup;
	}


	protected function checkInsertObject(IdentifiedObject $userGroup): void
	{
		//TODO
	}


	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		// TODO: verify user dependencies
		return parent::delete($request, $response, $args);
	}


	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
			'description' => new Assert\Type(['type' => 'string']),
			'type' => new Assert\Type(['type' => 'integer']),
		]);
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
