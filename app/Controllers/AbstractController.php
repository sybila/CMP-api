<?php

namespace App\Controllers;

use Doctrine\ORM\EntityManager;
use Slim\Container;
use Slim\Http\Response;

abstract class AbstractController
{
	/** @var EntityManager */
	protected $orm;

	public function __construct(Container $c)
	{
		$this->orm = $c->get('em');
	}

	protected static function formatOk(Response $response, array $data = null): Response
	{
		return $response->withStatus(200)->withJson([
			'status' => 'ok',
			'code' => 200,
			'data' => $data,
		]);
	}

	protected static function formatError(Response $response, int $code, string $message, ...$args): Response
	{
		return $response->withStatus(400)->withJson([
			'status' => 'error',
			'message' => sprintf($message, ...$args),
			'code' => $code,
		]);
	}

	protected static function formatPairs(iterable $collection, callable $callback): array
	{
		$ret = [];
		foreach ($collection as $value)
			$ret[] = $callback($value);
		return $ret;
	}
}
