<?php

namespace App\Controllers;

use App\Entity\{Authorization\User,
    Authorization\UserGroup,
    Authorization\UserGroupToUser,
    Authorization\UserType,
    IdentifiedObject};
use Doctrine\Common\Collections\Criteria;
use IGroupRoleAuthWritableController;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use League\OAuth2\Server\ResourceServer;
use stdClass;
use Symfony\Component\Mailer\Exception\InvalidArgumentException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use App\Entity\Repositories\IEndpointRepository;
use App\Repositories\Authorization\UserRepository;
use App\Exceptions\{ActionConflictException,
    DependentResourcesBoundException,
    InternalErrorException,
    InvalidAuthenticationException,
    InvalidRoleException,
    MissingRequiredKeyException,
    NonExistingObjectException,
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
class UserController extends WritableRepositoryController implements IGroupRoleAuthWritableController
{

	/** @var string */
	private $mailer;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->mailer = $c['mailer'];
	}


	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}


	protected function getData(IdentifiedObject $user): array
	{
		/** @var User $user */
		return [
			'id' => $user->getId(),
			'username' => $user->getUsername(),
			'name' => $user->getName(),
			'surname' => $user->getSurname(),
            'email' => $user->getEmail(),
			'type' => [
			    'id' => $user->getType()->getId(),
                'tier' => $user->getType()->getTier(),
                'name' => $user->getType()->getName()],
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
		!$body->hasKey('type') ?: $this->adminCheck() &&
            $user->setType($this->getUserType($body->getString('type')));
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
	    if ($this->userPermissions['platform_wise'] != User::ADMIN) {
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
        $user->setType($this->getUserType(User::TEMPORARY));
        $this->sendConfirmationMail($user->getEmail());
        $this->setDefaultUserSpaceGroup($user);

    }

    protected function getUserType(int $tier)
    {
        /** @var UserType $ut */
        $ut = $this->orm->getRepository(UserType::class)->findOneBy(['tier' => $tier]);
	    return $ut;
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

    public function getNewPsw (Request $request, Response $response, ArgumentParser $args): Response
    {
        $body = new ArgumentParser($request->getParsedBody());
        if ($body->hasKey('email')){
            $this->validate($body, $this->getValidator());
            $mail = $body->getString('email');
            /** @var User $usr */
            $usr = $this->orm->getRepository(User::class)->findOneBy(['email' => $mail]);
            if ($usr){
                $hash = sha1($this->mailer['salt'] . $mail);
                $url = $_SERVER['REQUEST_SCHEME'] . '://' . $this->mailer['client_srv_redirect'] . '/' . $mail . '/pswRenew/' . $hash;
                $this->sendNotificationEmail("Hello {$usr->getUsername()}. To generate new password click on this link <a href=$url>this link</a>." .
                    "Ignore this e-mail if you did not ask for a new password generation.", $mail);
                return self::formatOk($response, ['Renewal email sent.']);
            }
            else throw new NonExistingObjectException(0, $mail);
        }
        else throw new MissingRequiredKeyException('email');
    }

    public function generateNewPsw (Request $request, Response $response, ArgumentParser $args): Response
    {
        $body = new ArgumentParser($request->getParsedBody());
        $users = $this->orm->getRepository(User::class)->findBy(['email' => $args['email']]);
        //there should be only one, because of uniqueCheck.
        /** @var User $user */
        $user = current($users);
        if ($user === false)
            throw new InvalidAuthenticationException("User registered under this email does not exist.",
                "Try signing up");
        if ( sha1($this->mailer['salt'] . $user->getEmail()) === $args['hash']) {
            if ($body->hasKey('password')){
                $user->setPasswordHash($user->hashPassword($body->getString('password')));
                $this->orm->persist($user);
                $this->orm->flush();
                return self::formatOk($response, ['Password changed.']);
            }
            else throw new MissingRequiredKeyException('password');
        }
        else throw new ActionConflictException('Renewal link is malformed');
    }

    //----- RESOURCE PROTECTION ----//
    public function hasAccessToObject(array $userGroups): ?int
    {
        $rootRouteParent = self::getRootParent();
        //if there is no id, it means GET LIST was requested.
        if (is_null($rootRouteParent['id'])) {
            return null;
        }
        //user can always access himself
        if ($this->userPermissions['user_id'] == $rootRouteParent['id']) {
            return $this->userPermissions['user_id'];
        }
        if ($rootRouteParent['type'] == 'users') {
            /** @var User $user */
            $user = $this->getObjectViaORM(User::class, $rootRouteParent['id']);
            $relation = $user->getGroups()->filter(function (UserGroupToUser $groupLink) use ($userGroups){
                /** @var UserGroup $group */
               $group = $groupLink->getUserGroupId();
               return key_exists($group->getId(), $userGroups) && ($group->getId() != UserGroup::PUBLIC_SPACE);
            })->toArray();
            if (count($relation) || $user->getIsPublic()){
                return $user->getId();
            }
            else {
                throw new InvalidAuthenticationException("You cannot access this resource.",
                    "Not public or not a member of the same group.");
            }
        }
        throw new InvalidAuthenticationException('','');
    }

    public function getAccessFilter(array $userGroups): ?array
    {
        return ['id' => $this->getVisibleUsersId($userGroups)];
    }

    public function getVisibleUsersId(array $fromGroups)
    {
        $publicUsers = $this->orm->getRepository(User::class)
            ->matching(Criteria::create()->where(Criteria::expr()->eq('isPublic', true)))
            ->map(function (User $user) { return $user->getId(); })->toArray();
        $groupRepo = $this->orm->getRepository(UserGroup::class);
        foreach ($fromGroups as $id => $role){
            if($id != UserGroup::PUBLIC_SPACE){
                $group = $groupRepo->find($id)->getUsers()->map(function (UserGroupToUser $groupLink) {
                    $user = $groupLink->getUserId();
                    return $user->getId();
                })->toArray();
                $publicUsers = array_merge($publicUsers, $group);
            }
        }
        return array_unique($publicUsers);
    }

    public function canAdd(int $role, ?int $id): bool
    {
        return true;
    }

    public function canEdit(int $role, int $id): bool
    {
        $parent = self::getRootParent();
        if ($id != $parent['id']){
            return false;
        } else {
            return true;
        }
    }

    public function canDelete(int $role, int $id): bool
    {
        return $this->canEdit($role, $id);
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
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $this->mailer['client_srv_redirect'] . '/' . $receiver . '/' . $hash;
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

    protected function sendNotificationEmail($message, $receiver){
        try {
            $transport = Transport::fromDsn($this->mailer['dsn']);
        }
        catch (InvalidArgumentException $e) {
            throw new MissingRequiredKeyException('dsn is not set up properly.');
        }
        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from('TODO@mail.muni.cz')
            ->to($receiver)
            ->subject('CMP: Account notification.')
            ->html("<p>$message</p>");
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
        /** @var User $user */
        $user = current($users);
        if ($user === false)
            throw new InvalidAuthenticationException("User registered under this email does not exist.",
                "Try signing up");
        if ($user->getType()->getTier() <= User::REGISTERED)
            throw new ActionConflictException("This user has already confirmed the registration");
        if ( sha1($user->getEmail() . $this->mailer['salt']) === $args['hash']) {
            $user->setType($this->getUserType(User::REGISTERED));
            $this->orm->persist($user);
            $this->setDefaultUserSpaceGroup($user);
            $this->orm->flush();
            return self::formatOk($response, ['Registration confirmed.']);
        }
        else throw new ActionConflictException('Confirmation link is malformed');
    }

    protected function setDefaultUserSpaceGroup(User $user)
    {
        $mySpace = new UserGroup();
        $mySpace->setName("Private space of user {$user->getName()}");
        $mySpace->setType(4);
        $mySpace->setDescription('This is your own sandbox to create and modify models, experiments.' .
        'Link them to BCS to unlock the full potential of CMP.');
        $mySpace->setIsPublic((int)false);
        $linkToMySpace = new UserGroupToUser();
        $linkToMySpace->setUserId($user);
        $linkToMySpace->setUserGroupId($mySpace);
        $linkToMySpace->setRoleId(User::OWNER_ROLE);
        $this->orm->persist($mySpace);
        $this->orm->persist($linkToMySpace);
    }

}

final class LoggedInUserController extends UserController
{

    /**
     * @inheritDoc
     * @throws InvalidAuthenticationException
     */
    public function readIdentified(Request $request, Response $response, ArgumentParser $args) : Response
    {
        $this->isAuthorized($request->getAttribute('oauth_user_id'));
        $myArgs = new ArgumentParser(['id' => $request->getAttribute('oauth_user_id')]);
        return parent::readIdentified($request, $response, $myArgs);
    }

    /**
     * @inheritDoc
     * @throws InvalidAuthenticationException
     */
    public function edit(Request $request, Response $response, ArgumentParser $args): Response
    {
        $this->isAuthorized($request->getAttribute('oauth_user_id'));
        $myArgs = new ArgumentParser(['id' => $request->getAttribute('oauth_user_id')]);
        return parent::edit($request, $response, $myArgs);
    }

    /**
     * @inheritDoc
     * @throws InvalidAuthenticationException
     */
    public function delete(Request $request, Response $response, ArgumentParser $args): Response
    {
        $this->isAuthorized($request->getAttribute('oauth_user_id'));
        $myArgs = new ArgumentParser(['id' => $request->getAttribute('oauth_user_id')]);
        return parent::delete($request, $response, $myArgs);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param ArgumentParser $args
     * @return Response
     * @throws mixed
     */
    public function resendCnfEmail(Request $request, Response $response, ArgumentParser $args): Response
    {
        $this->isAuthorized($request->getAttribute('oauth_user_id'));
        /** @var User $authUser */
        $authUser = $this->getObjectViaORM(User::class, $request->getAttribute('oauth_user_id'));
        $mail = $authUser->getEmail();
        $body = new ArgumentParser($request->getParsedBody());
        if ($body->hasKey('email')){
            $this->validate($body, $this->getValidator());
            $mail = $body->getString('email');
            $this->uniqueCheck('email', $mail);
            $authUser->setEmail($mail);
        }
        $this->sendConfirmationMail($authUser->getEmail());
        $this->orm->persist($authUser);
        $this->orm->flush();
        return self::formatOk($response, ['receiver' => $mail]);
    }


    /**
     * @param int|null $id
     * @throws InvalidAuthenticationException
     */
    private function isAuthorized(?int $id)
    {
        if(is_null($id)) {
            throw new InvalidAuthenticationException('User not authorized.',  'This endpoint is accessible' .
            ' only with valid token.');
        }
    }
}
