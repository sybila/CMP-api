<?php

use Slim\Container;
use App\Helpers;
use Slim\Http\Request;
use Slim\Http\Response;

$config = require __DIR__ . '/../app/settings.php';

$c = new Container($config);
unset($c['errorHandler']);
unset($c['phpErrorHandler']);
unset($c['view']);
unset($c['logger']);

\Tracy\Debugger::enable($c->settings['tracy']['mode'], $c->settings['tracy']['logDir']);
\Tracy\Debugger::timer('execution');

// Doctrine
$c['em'] = function (Container $c)
{
	$settings = $c->settings;
	$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
		$settings['doctrine']['meta']['entity_path'],
		$settings['doctrine']['meta']['auto_generate_proxies'],
		$settings['doctrine']['meta']['proxy_dir'],
		$settings['doctrine']['meta']['cache'],
		false
	);

	$config->addCustomStringFunction('TYPE', \App\Doctrine\ORM\Query\Functions\TypeFunction::class);

	return \Doctrine\ORM\EntityManager::create($settings['doctrine']['connection'], $config);
};

$c['foundHandler'] = function (Container $c)
{
	return new Helpers\RequestResponseParsedArgs;
};

$c['notFoundHandler'] = function (Container $c)
{
	return function(Request $request, Response $response)
	{
		$response->withStatus(404);
		return $response->withJson([
			'status' => 'error',
			'message' => 'Page not found',
			'code' => 404,
		]);
	};
};

$c['errorHandler'] = function(Container $c)
{
	return function(Request $request, Response $response, \Throwable $exception)
	{
		if ($exception instanceof \App\Exceptions\ApiException)
			return $response->withStatus($exception->getHttpCode())->withJson([
					'status' => 'error',
					'code' => $exception->getCode(),
					'message' => $exception->getMessage(),
				] + $exception->getAdditionalData());

		if (!\Tracy\Debugger::$productionMode)
			throw $exception;

		\Tracy\Debugger::log($exception);
		$response->withStatus(500);
		return $response->withJson([
			'status' => 'error',
			'code' => 500,
			'message' => '',
		]);
	};
};

return $c;
