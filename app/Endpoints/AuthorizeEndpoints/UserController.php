<?php

namespace App\Controllers;

use App\Entity\{
	Authorization\User,
	Authorization\UserGroup,
	IdentifiedObject
};
use App\Entity\Repositories\IEndpointRepository;
use App\Repositories\Authorization\UserRepository;
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
 * @property-read UserRepository $repository
 * @method Model getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class UserController extends WritableRepositoryController
{

	/** @var UserRepository */
	private $userRepository;


	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->userRepository = $c->get(UserRepository::class);
	}


	protected static function getAllowedSort(): array
	{
		return ['id, name'];
	}


	protected function getData(IdentifiedObject $user): array
	{
		/** @var User $user */
		return [
			'id' => $user->getId(),
			'name' => $user->getName(),
			'surname' => $user->getSurname(),
			'groups' => $user->getGroups()->map(function (UserGroup $group) {
					return ['id' => $group->getId(), 'name' => $group->getName()];
				})->toArray(),
		];
	}


	protected function setData(IdentifiedObject $user, ArgumentParser $body): void
	{
		/** @var User $user */
	}


	protected function createObject(ArgumentParser $body): IdentifiedObject
	{

	}


	protected function checkInsertObject(IdentifiedObject $user): void
	{

	}


	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{

	}


	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
				'userId' => new Assert\Type(['type' => 'integer']),
				'description' => new Assert\Type(['type' => 'string']),
				'visualisation' => new Assert\Type(['type' => 'string']),
				'status' => new Assert\Type(['type' => 'string']),
		]));
	}


	protected static function getObjectName(): string
	{
		return 'user';
	}


	protected static function getRepositoryClassName(): string
	{
		return UserRepository::Class;
	}

}
