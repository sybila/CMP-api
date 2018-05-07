<?php

namespace App\Controllers;

use App\Entity\AnnotationTerm;
use App\Exceptions\MissingRequiredKeyException;
use App\Helpers\ArgumentParser;
use Slim\Http\Request;
use Slim\Http\Response;

final class AnnotationController extends AbstractController
{
	public function readTypes(Request $request, Response $response, ArgumentParser $args): Response
	{
		$data = [];
		foreach (AnnotationTerm::$names as $id => $name)
			$data[] = ['code' => $id, 'name' => $name];

		return self::formatOk($response, $data);
	}

	public function readLink(Request $request, Response $response, ArgumentParser $args): Response
	{
		if (!$args->hasKey('type') || !$args->hasKey('id') || $args->getString('id') == '')
			throw new MissingRequiredKeyException('Missing type or id');

		$term = AnnotationTerm::tryGet('type', $args->getString('type'));
		return self::formatOk($response, ['url' => $term->getLink($args->getString('id'))]);
	}
}
