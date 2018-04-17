<?php

use App\Controllers;
use App\Helpers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;

require '../vendor/autoload.php';

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

$app = new \Slim\App($c);
$app->get('/', function (Request $request, Response $response, Helpers\ArgumentParser $args)
{
    return $response->withRedirect('/version');
});

$app->get('/version', Controllers\VersionController::class);
$app->get('/entities', Controllers\EntityController::class . ':read');
$app->get('/entities/{id:\\d+}', Controllers\EntityController::class . ':readOne');
$app->post('/entities', Controllers\EntityController::class . ':add');
$app->put('/entities/{id:\\d+}', Controllers\EntityController::class . ':edit');
$app->delete('/entities/{id:\\d+}', Controllers\EntityController::class . ':delete');
$app->get('/rules', Controllers\RuleController::class . ':read');
$app->get('/rules/{id:\\d+}', Controllers\RuleController::class . ':readOne');
//$app->post('/rules', Controllers\RuleController::class . ':add');
//$app->put('/rules/{id:\\d+}', Controllers\RuleController::class . ':edit');
//$app->delete('/rules/{id:\\d+}', Controllers\RuleController::class . ':delete');
$app->get('/organisms', Controllers\OrganismController::class . ':read');
$app->get('/classifications[/{type}]', Controllers\ClassificationController::class . ':read');
//$app->get('/classifications', Controllers\ClassificationController::class . ':read');

if (!\Tracy\Debugger::$productionMode)
{
	$panel = new App\Helpers\DoctrineTracyPanel;
	\Tracy\Debugger::getBar()->addPanel($panel);
	/** @var \Doctrine\ORM\EntityManager $em */
	$em = $c['em'];
	$em->getConfiguration()->setSQLLogger($panel);
}

$app->add(function(Request $request, Response $response, callable $next) {
	/** @var Response $response */
	$response = $next($request, $response);

	if (!\Tracy\Debugger::$productionMode)
	{
		$json = json_decode((string)$response->getBody());
		$body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
		$body->write('<pre>' . json_encode($json, JSON_PRETTY_PRINT));
		return $response->withHeader('Content-type', 'text/html')->withBody($body);
	}
	else
	{
		$runtime = round(\Tracy\Debugger::timer('execution') * 1000, 3);
		return $response->withHeader('X-Run-Time', $runtime . 'ms');
	}
});

$app->run();
