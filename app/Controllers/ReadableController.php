<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Helpers\ArgumentParser;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class ReadableController extends AbstractController
{
	abstract protected function getEntity(int $id);
	abstract protected function getData($entity): array;
	abstract protected function getSingleData($entity): array;

	public function readOne(Request $request, Response $response, ArgumentParser $args): Response
	{
		$entity = $this->getEntity($args->getInt('id'));
		return self::formatOk($response, $this->getData($entity) + $this->getSingleData($entity));
	}

	public static function identifierGetter(): \Closure
	{
		return function(IdentifiedObject $object) { return $object->getId(); };
	}
}
