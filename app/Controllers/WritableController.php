<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Helpers\ArgumentParser;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class WritableController extends ReadableController
{
	abstract protected function setData($entity, ArgumentParser $data): void;
	abstract protected function createEntity(ArgumentParser $data): IdentifiedObject;

	public function add(Request $request, Response $response, ArgumentParser $args): Response
	{
		$body = new ArgumentParser($request->getParsedBody());
		$entity = $this->createEntity($body);
		$this->setData($entity, $body);
		$this->orm->persist($entity);
		$this->orm->flush();
		return self::formatInsert($response, $entity->getId());
	}

	public function edit(Request $request, Response $response, ArgumentParser $args): Response
	{
		$entity = $this->getEntity($args->getInt('id'));
		$this->setData($entity, new ArgumentParser($request->getParsedBody()));
		$this->orm->persist($entity);
		$this->orm->flush();
		return self::formatOk($response);
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$entity = $this->getEntity($args->getInt('id'));
		$this->orm->remove($entity);
		$this->orm->flush();
		return self::formatOk($response);
	}
}
