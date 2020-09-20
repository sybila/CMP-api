<?php

namespace App\Controllers;

use App\Entity\{
	Authorization\User,
	Authorization\UserGroup,
	Authorization\UserGroupToUser,
	IdentifiedObject
};
use Symfony\Component\Mailer\Exception\InvalidArgumentException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use App\Entity\Repositories\IEndpointRepository;
use App\Repositories\Authorization\UserRepository;
use App\Exceptions\{ActionConflictException,
    DependentResourcesBoundException,
    InvalidRoleException,
    MissingRequiredKeyException,
    UniqueKeyViolationException};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request,
	Response
};
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read UserRepository $repository
 * @method User getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class UserController extends WritableRepositoryController
{

	/** @var UserRepository */
	private $userRepository;

	/** @var string */
	private $mailer;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->userRepository = $c->get(UserRepository::class);
		$this->mailer = $c['mailer'];
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
		!$body->hasKey('username') ?: $this->uniqueCheck('username', $body->getString('username')) &&
             $user->setName($body->getString('username'));
		!$body->hasKey('password') ?: $user->setPasswordHash($user->hashPassword($body->getString('password')));
		!$body->hasKey('name') ?: $user->setName($body->getString('name'));
		!$body->hasKey('surname') ?: $user->setSurname($body->getString('surname'));
		!$body->hasKey('type') ?: $this->adminCheck() && $user->setType($body->getString('type'));
		!$body->hasKey('email') ?:  $this->uniqueCheck('email', $body->getString('email')) &&
            $user->setEmail($body->getString('email'));
		!$body->hasKey('phone') ?: $user->setPhone($body->getString('phone'));
        !$body->hasKey('isPublic') ?: $user->setIsPublic($body->getString('isPublic'));
        //!$body->hasKey('groups') ?: $user->setGroups($body->getString('groups'));
	}

	protected function uniqueCheck(string $attribute, string $value) {
        if (!$this->orm->getRepository(User::class)->findBy([$attribute => $value]) == null)
            throw new UniqueKeyViolationException($attribute,null, 'user');
        return true;
    }

    protected function adminCheck() {
	    if ($this->user_permissions['platform_wise'] != User::ADMIN) {
	        throw new InvalidRoleException('Admin permissions are needed. Cannot change user type ',
                'PUT', $_SERVER['REQUEST_URI']);
        }
	    return true;
    }


	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		$this->verifyMandatoryArguments(['username', 'password', 'name', 'surname', 'email'], $body);
		return new User($body['username']);
	}


	protected function checkInsertObject(IdentifiedObject $user): void
	{
		/** @var User $user */
		if ($user->getUsername() === null)
			throw new MissingRequiredKeyException('username');
		if ($user->getName() === null)
			throw new MissingRequiredKeyException('name');
		if ($user->getSurname() === null)
			throw new MissingRequiredKeyException('surname');
        if ($user->getIsPublic() === null)
            throw new MissingRequiredKeyException('isPublic');
        //FIXME following lines should not be here
        $user->setType(User::TEMPORARY);
        $this->sendConfirmationMail($user->getEmail());

    }


	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		// TODO: verify group dependencies, fuck you
		$response = parent::delete($request, $response, $args);
		$this->sendNotificationEmail('Your account has been deleted. Bye.',
            $entity = $this->getObject($this->getModifyId($args))->getEmail());
		return $response;
	}


	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'username' => new Assert\Type(['type' => 'string']),
			'password' => new Assert\Type(['type' => 'string']),
			'name' => new Assert\Type(['type' => 'string']),
			'surname' => new Assert\Type(['type' => 'string']),
			'type' => new Assert\Type(['type' => 'integer']),
			'email' => new Assert\Email(),
			'phone' => new Assert\Type(['type' => 'string']),
            'isPublic' => new Assert\Type(['type' => 'int'])
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

    //-----MAIL NOTIFICATION HELPERS
    //FIXME move me to some other place, once the platform notifications are implemented

    protected function sendConfirmationMail($receiver){
        try {
            $transport = Transport::fromDsn($this->mailer['dsn']);
        }
        catch (InvalidArgumentException $e) {
            throw new MissingRequiredKeyException('dsn is not set up properly.');
        }
        $hash = sha1($receiver . $this->mailer['salt']);
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/users/' . $receiver . '/' . $hash;
        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from('ecyano@fi.muni.cz')
            ->to($receiver)
            ->subject('CMP: Confirm your registration')
            ->html("<p>If you want to fully activate your account click on <a href=$url>this link</a></p>");
        try {
            $mailer->send($email);
        }
        catch (TransportExceptionInterface $e){
            throw new MissingRequiredKeyException($e->getMessage() . $e->getDebug());
        }
    }

    public function confirmRegistration(Request $request, Response $response, ArgumentParser $args): Response
    {
        $users = $this->orm->getRepository(User::class)->findBy(['email' => $args['email']]);
        //there should be only one, because of uniqueCheck.
        foreach ($users as $user)
            if ($user->getType() <= User::REGISTERED)
                throw new ActionConflictException("This user has already confirmed the registration");
            !sha1($user->getEmail() . $this->mailer['salt']) === $args['hash'] ?: $user->setType(3);
        $this->orm->persist($user);
        $this->orm->flush();
        return self::formatOk($response, ['Registration confirmed.']);
    }

    protected function sendNotificationEmail($message, $receiver){
        $transport = Transport::fromDsn($this->mailer['dsn']);
        $mailer = new Mailer($transport);
        $link = str_shuffle(md5($receiver));
        $email = (new Email())
            ->from('TODO@mail.muni.cz')
            ->to($receiver)
            ->subject('CMP: Account notification.')
            ->text($message)
            ->html("<p>$message</p>");
        $mailer->send($email);
    }
}
