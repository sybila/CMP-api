<?php

namespace App\Controllers;

use App\Entity\Authorization\User;
use App\Entity\IdentifiedObject;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\InvalidAuthenticationException;
use App\Exceptions\InvalidRoleException;
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exceptions\MissingRequiredKeyException;

abstract class WritableRepositoryController extends RepositoryController
{

	use ValidatedController;

	/** @var \stdClass */
	private $data;

	/**
	 * function($entity)
	 * @var callable[]
	 */
	protected $beforeInsert = [];

	/**
	 * function($entity)
	 * @var callable[]
	 */
	protected $beforeUpdate = [];

	/**
	 * function($entity)
	 * @var callable[]
	 */
	protected $beforeDelete = [];


	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->data = $c['persistentData'];
	}


	/**
	 * fill $object with data from $body, do additional validations
	 * @param IdentifiedObject $object
	 * @param ArgumentParser   $body
	 */
	abstract protected function setData(IdentifiedObject $object, ArgumentParser $body): void;


	/**
	 * Create object to be inserted, can be as simple as `return new SomeObject;`
	 * @param ArgumentParser $body request body
	 * @return IdentifiedObject
	 */
	abstract protected function createObject(ArgumentParser $body): IdentifiedObject;


	/**
	 * Check object to be inserted if it contains all required fields
	 * @param IdentifiedObject $object
	 */
	abstract protected function checkInsertObject(IdentifiedObject $object): void;


	protected function getModifyId(ArgumentParser $args): int
	{
		return $args->getInt('id');
	}


	public function add(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);
        $this->validateAdd($this->getAccess($request));
		$body = new ArgumentParser($request->getParsedBody());
		$this->validate($body, $this->getValidator());
		$object = $this->createObject($body);
		$this->setData($object, $body);
		$this->checkInsertObject($object);

		$this->runEvents($this->beforeInsert, $object);

		$this->orm->persist($object);
		//FIXME: flush shouldn't be called here but in FlushMiddleware, but then we can't get inserted object id
		$this->orm->flush();
		return self::formatInsert($response, $object->getId());
	}


	public function edit(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);
		$object = $this->getObject($this->getModifyId($args));
        $this->validateEdit($this->getAccess($request));
		$body = new ArgumentParser($request->getParsedBody());
		$this->validate($body, $this->getValidator());
		$this->setData($object, $body);

		$this->runEvents($this->beforeUpdate, $object);

		$this->orm->persist($object);
		$this->data->needsFlush = true;
		return self::formatOk($response);
	}


	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);
		$entity = $this->getObject($this->getModifyId($args));
        $this->validateDelete($this->getAccess($request));
		$this->runEvents($this->beforeDelete, $entity);
		$this->orm->remove($entity);
		$this->data->needsFlush = true;
		return self::formatOk($response);
	}


	/**
	 * Iterate over array of argument names, throw exception if an argument is missing
	 * @param array $keys
	 * @param ArgumentParser $body
	 * @return void
	 * @throws MissingRequiredKeyException
	 */
	protected function verifyMandatoryArguments(array $keys, ArgumentParser $body): void
	{
		foreach ($keys as $key) {
			if (!$body->hasKey($key)) {
				throw new MissingRequiredKeyException($key);
			}
		}
	}


	abstract protected function getValidator(): Assert\Collection;

    /**
     * @param array $user_permissions
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidRoleException|InvalidAuthenticationException
     */
    public function validateAdd(array $user_permissions) : bool
    {
        switch ($user_permissions['platform_wise']) {
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
                $user_group = $this->hasAccessToObject($user_permissions['group_wise']);
                dump('penis');
                if(!is_null($user_group) &&
                    !$this->canAdd($user_permissions['group_wise'][$user_group], $user_permissions['user_id']))
                {
                    throw new InvalidRoleException("add $user_group", 'POST',
                        $_SERVER['REQUEST_URI']);
                }
                return true;
            case User::TEMPORARY:
            case User::GUEST:
                if(!$this->canAdd($user_permissions['group_wise'][1], $user_permissions['user_id']))
                    throw new InvalidRoleException('add', 'POST', $_SERVER['REDIRECT_URL']);
                return true;
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @param array $user_permissions
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidAuthenticationException
     * @throws InvalidRoleException
     */
    public function validateEdit(array $user_permissions) : bool
    {
        switch ($user_permissions['platform_wise']) {
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
                $user_group = $this->hasAccessToObject($user_permissions['group_wise']);
                if (!$this->canManipulate($user_permissions['group_wise'][$user_group],
                    $user_permissions['user_id'], User::CAN_EDIT)) {
                    throw new InvalidRoleException('edit', 'PUT',
                        $_SERVER['REDIRECT_URL']);
                }
                return true;
            case User::TEMPORARY:
            case User::GUEST:
                throw new InvalidRoleException('edit', 'PUT', $_SERVER['REDIRECT_URL']);
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @param array $user_permissions
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidAuthenticationException
     * @throws InvalidRoleException
     */
    public function validateDelete(array $user_permissions) : bool
    {
        switch ($user_permissions['platform_wise']) {
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
                $user_group = $this->hasAccessToObject($user_permissions['group_wise']);
                if (!$this->canManipulate($user_permissions['group_wise'][$user_group],
                    $user_permissions['user_id'], User::CAN_DELETE)) {
                    throw new InvalidRoleException('delete', 'DELETE',
                        $_SERVER['REDIRECT_URL']);
                }
                return true;
            case User::TEMPORARY:
            case User::GUEST:
                throw new InvalidRoleException('delete', 'DELETE', $_SERVER['REDIRECT_URL']);
            default:
                throw new InvalidArgumentException('user_type', $user_permissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }
}
