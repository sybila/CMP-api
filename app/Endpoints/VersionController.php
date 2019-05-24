<?php

namespace App\Controllers;

use App\Helpers\ArgumentParser;
use Slim\Http\Request;
use Slim\Http\Response;

class VersionController extends AbstractController
{
	public function __invoke(Request $request, Response $response, ArgumentParser $args)
	{
		return self::formatOk($response, ['version' => '0.2']);
	}
}
