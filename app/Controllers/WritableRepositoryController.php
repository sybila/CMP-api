<?php

namespace App\Controllers;

use App\Entity\Authorization\User;
use App\Entity\IdentifiedObject;
use App\Exceptions\InternalErrorException;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\InvalidAuthenticationException;
use App\Exceptions\InvalidRoleException;
use App\Exceptions\InvalidTypeException;
use App\Exceptions\MalformedInputException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use IGroupRoleAuthWritableController;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use stdClass;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exceptions\MissingRequiredKeyException;

abstract class WritableRepositoryController extends RepositoryController implements IGroupRoleAuthWritableController
{

	use ValidatedController;

	/** @var stdClass */
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
     * @throws InvalidTypeException
	 */
	abstract protected function setData(IdentifiedObject $object, ArgumentParser $body): void;


	/**
	 * Create object to be inserted, can be as simple as `return new SomeObject;`
	 * @param ArgumentParser $body request body
	 * @return IdentifiedObject
     * @throws MissingRequiredKeyException
	 */
	abstract protected function createObject(ArgumentParser $body): IdentifiedObject;


	/**
	 * Check object to be inserted if it contains all required fields
	 * @param IdentifiedObject $object
     * @throws MissingRequiredKeyException
	 */
	abstract protected function checkInsertObject(IdentifiedObject $object): void;


	protected function getModifyId(ArgumentParser $args): int
	{
		return $args->getInt('id');
	}


	public function add(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);
        $this->validateAdd();
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

    /**
     * @param Request $request
     * @param Response $response
     * @param ArgumentParser $args
     * @return Response
     * @throws InternalErrorException
     * @throws InvalidArgumentException
     * @throws InvalidRoleException
     * @throws InvalidTypeException
     * @throws NonExistingObjectException
     * @throws ORMException
     * @throws MalformedInputException
     */
	public function edit(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);
		$object = $this->getObject($this->getModifyId($args));
        $this->validateEdit();
		$body = new ArgumentParser($request->getParsedBody());
		$this->validate($body, $this->getValidator());
		$this->setData($object, $body);

		$this->runEvents($this->beforeUpdate, $object);

		$this->orm->persist($object);
		$this->data->needsFlush = true;
		return self::formatOk($response);
	}

    /**
     * @param Request $request
     * @param Response $response
     * @param ArgumentParser $args
     * @return Response
     * @throws InternalErrorException
     * @throws InvalidArgumentException
     * @throws InvalidRoleException
     * @throws NonExistingObjectException
     * @throws ORMException
     */
	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);
		$entity = $this->getObject($this->getModifyId($args));
        $this->validateDelete();
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
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidRoleException
     */
    public function validateAdd(): bool
    {
        switch ($this->userPermissions['platform_wise']) {
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
                $user_group = $this->hasAccessToObject($this->userPermissions['group_wise']);
                if(!is_null($user_group) &&
                    !$this->canAdd($this->userPermissions['group_wise'][$user_group], $this->userPermissions['user_id']))
                {
                    throw new InvalidRoleException("add $user_group", 'POST',
                        $_SERVER['REQUEST_URI']);
                }
                return true;
            case User::TEMPORARY:
            case User::GUEST:
                if(!$this->canAdd($this->userPermissions['group_wise'][1], $this->userPermissions['user_id']))
                    throw new InvalidRoleException('add', 'POST', $_SERVER['REQUEST_URI']);
                return true;
            default:
                throw new InvalidArgumentException('user_type', $this->userPermissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidRoleException
     */
    public function validateEdit(): bool
    {
        switch ($this->userPermissions['platform_wise']) {
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
                $user_group = $this->hasAccessToObject($this->userPermissions['group_wise']);
                if (!$this->canEdit($this->userPermissions['group_wise'][$user_group],
                    $this->userPermissions['user_id'])) {
                    throw new InvalidRoleException('edit', 'PUT',
                        $_SERVER['REDIRECT_URL']);
                }
                return true;
            case User::TEMPORARY:
            case User::GUEST:
                throw new InvalidRoleException('edit', 'PUT', $_SERVER['REDIRECT_URL']);
            default:
                throw new InvalidArgumentException('user_type', $this->userPermissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidRoleException
     */
    public function validateDelete(): bool
    {
        switch ($this->userPermissions['platform_wise']) {
            case User::ADMIN:
                return true;
            case User::POWER:
            case User::REGISTERED:
                $user_group = $this->hasAccessToObject($this->userPermissions['group_wise']);
                if (!$this->canDelete($this->userPermissions['group_wise'][$user_group],
                    $this->userPermissions['user_id'])) {
                    throw new InvalidRoleException('delete', 'DELETE',
                        $_SERVER['REDIRECT_URL']);
                }
                return true;
            case User::TEMPORARY:
            case User::GUEST:
                throw new InvalidRoleException('delete', 'DELETE', $_SERVER['REDIRECT_URL']);
            default:
                throw new InvalidArgumentException('user_type', $this->userPermissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }
}
