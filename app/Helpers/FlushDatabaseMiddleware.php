<?php

namespace App\Helpers;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class FlushDatabaseMiddleware
{
	/** @var EntityManager */
	private $em;

	/** @var \stdClass */
	private $data;

	public function __construct(ContainerInterface $container)
	{
		$this->em = $container->get(EntityManager::class);
		$this->data = $container->get('persistentData');
	}

	public function __invoke(Request $request, Response $response, callable $next)
	{
		/** @var Response $response */
		$response = $next($request, $response);
		if ($this->data->needsFlush)
			$this->em->flush();

		return $response;
	}
}
