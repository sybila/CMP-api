<?php

namespace App\Controllers;

use App\Entity\{
	Authorization\User,
	Authorization\UserGroup,
	Authorization\UserGroupToUser,
	IdentifiedObject
};
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Repositories\IEndpointRepository;
use App\Repositories\Authorization\UserRepository;
use App\Exceptions\{
	DependentResourcesBoundException
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
			'username' => $user->getUsername(),
			'name' => $user->getName(),
			'surname' => $user->getSurname(),
			'type' => (int) $user->getType(),
			'groups' => $user->getGroups()->map(function (UserGroupToUser $groupLink) {
					$group = $groupLink->getUserGroupId();
					return ['id' => $group->getId(), 'role' => (int) $groupLink->getRoleId(), 'name' => $group->getName()];
				})->toArray(),
		];
	}


	protected function setData(IdentifiedObject $user, ArgumentParser $body): void
	{
		/** @var User $user */
		!$body->hasKey('username') ?: $user->setName($body->getString('username'));
		!$body->hasKey('password') ?: $user->setPasswordHash($user->hashPassword($body->getString('password')));
		!$body->hasKey('name') ?: $user->setName($body->getString('name'));
		!$body->hasKey('surname') ?: $user->setSurname($body->getString('surname'));
		!$body->hasKey('type') ?: $user->setType($body->getString('type'));
		!$body->hasKey('email') ?: $user->setEmail($body->getString('email'));
		!$body->hasKey('phone') ?: $user->setPhone($body->getString('phone'));
		//!$body->hasKey('groups') ?: $user->setGroups($body->getString('groups'));
	}


	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		$this->verifyMandatoryArguments(['username', 'password', 'name', 'surname', 'type'], $body);
		return new User($body['username']);
	}


	protected function checkInsertObject(IdentifiedObject $user): void
	{
		/** @var User user */
		if ($user->getUsername() === null)
			throw new MissingRequiredKeyException('username');
		if ($user->getName() === null)
			throw new MissingRequiredKeyException('name');
		if ($user->getSurname() === null)
			throw new MissingRequiredKeyException('surname');
		if ($user->getType() === null)
			throw new MissingRequiredKeyException('type');
	}


	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		// TODO: verify group dependencies
		return parent::delete($request, $response, $args);
	}


	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'username' => new Assert\Type(['type' => 'string']),
			'password' => new Assert\Type(['type' => 'string']),
			'name' => new Assert\Type(['type' => 'string']),
			'surname' => new Assert\Type(['type' => 'string']),
			'type' => new Assert\Type(['type' => 'integer']),
			'email' => new Assert\Type(['type' => 'string']),
			'phone' => new Assert\Type(['type' => 'string']),
		]);
	}


	protected static function getObjectName(): string
	{
		return 'user';
	}


	protected static function getRepositoryClassName(): string
	{
		return UserRepository::Class;
	}

    protected static function getAlias(): string
    {
        return 'u';
    }
}
