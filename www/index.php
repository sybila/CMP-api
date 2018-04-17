<?php

use App\Controllers;
use App\Helpers;
use Slim\Http\Request;
use Slim\Http\Response;

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App(require __DIR__ . '/../app/dependecies.php');
$app->get('/', function (Request $request, Response $response, Helpers\ArgumentParser $args)
{
    return $response->withRedirect('/version');
});

$app->get('/version', Controllers\VersionController::class);
$app->get('/entities', Controllers\EntityController::class . ':read');
$app->get('/entities/{id:\\d+}', Controllers\EntityController::class . ':readOne');
$app->post('/entities', Controllers\EntityController::class . ':add');
$app->put('/entities/{id:\\d+}', Controllers\EntityController::class . ':edit');
$app->post('/entities/{id:\\d+}/status', Controllers\EntityController::class . ':editStatus');
$app->delete('/entities/{id:\\d+}', Controllers\EntityController::class . ':delete');
$app->get('/entities/{code}', Controllers\EntityController::class . ':readCode');
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
