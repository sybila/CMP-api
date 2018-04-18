<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Exceptions\MalformedInputException;
use App\Helpers\ArgumentParser;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class ReadableController extends AbstractController
{
	abstract protected function getEntity(int $id);
	abstract protected function getData($entity): array;
	abstract protected function getSingleData($entity): array;

	public function readIdentified(Request $request, Response $response, ArgumentParser $args): Response
	{
		// format should be checked in route
		$ids = $args->getString('id');
		$data = [];
		foreach (explode(',', $ids) as $id)
		{
			$entity = $this->getEntity((int)$id);
			$data[] = $this->getData($entity) + $this->getSingleData($entity);
		}

		return self::formatOk($response, $data);
	}

	public static function identifierGetter(): \Closure
	{
		return function(IdentifiedObject $object) { return $object->getId(); };
	}
}
