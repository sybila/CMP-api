<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Helpers\ArgumentParser;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Validator\Constraints as Assert;

abstract class WritableRepositoryController extends RepositoryController
{
	use ValidatedController;

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

		$body = new ArgumentParser($request->getParsedBody());
		$this->validate($body, $this->getValidator());
		$object = $this->createObject($body);
		$this->setData($object, $body);
		$this->checkInsertObject($object);

		$this->runEvents($this->beforeInsert, $object);

		$this->orm->persist($object);
		$this->orm->flush();
		return self::formatInsert($response, $object->getId());
	}

	public function edit(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);

		$object = $this->getObject($this->getModifyId($args));

		$body = new ArgumentParser($request->getParsedBody());
		$this->validate($body, $this->getValidator());
		$this->setData($object, $body);

		$this->runEvents($this->beforeUpdate, $object);

		$this->orm->persist($object);
		$this->orm->flush();
		return self::formatOk($response);
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);
		$entity = $this->getObject($this->getModifyId($args));
		$this->runEvents($this->beforeDelete, $entity);
		$this->orm->remove($entity);
		$this->orm->flush();
		return self::formatOk($response);
	}

	abstract protected function getValidator(): Assert\Collection;
}
