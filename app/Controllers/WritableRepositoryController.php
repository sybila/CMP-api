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

	abstract protected function setData($entity, ArgumentParser $body, bool $insert): void;
	abstract protected function createObject(ArgumentParser $body): IdentifiedObject;

	protected function getModifyId(ArgumentParser $args): int
	{
		return $args->getInt('id');
	}

	public function add(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);

		$body = new ArgumentParser($request->getParsedBody());
		$this->validate($body, $this->getValidator());
		$entity = $this->createObject($body);
		$this->setData($entity, $body, true);

		$this->runEvents($this->beforeInsert, $entity);

		$this->orm->persist($entity);
		$this->orm->flush();
		return self::formatInsert($response, $entity->getId());
	}

	public function edit(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);

		$entity = $this->getObject($this->getModifyId($args));

		$body = new ArgumentParser($request->getParsedBody());
		$this->validate($body, $this->getValidator());
		$this->setData($entity, $body, false);

		$this->runEvents($this->beforeUpdate, $entity);

		$this->orm->persist($entity);
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
