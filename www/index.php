<?php

use Slim\Http\Request;
use Slim\Http\Response;

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App(require __DIR__ . '/../app/dependecies.php');
(require __DIR__ . '/../app/routes.php')($app);

if (!\Tracy\Debugger::$productionMode)
{
	$panel = new App\Helpers\DoctrineTracyPanel;
	\Tracy\Debugger::getBar()->addPanel($panel);
	/** @var \Doctrine\ORM\EntityManager $em */
	$em = $c[\Doctrine\ORM\EntityManager::class];
	$em->getConfiguration()->setSQLLogger($panel);
}

$app->add(\App\Helpers\FlushDatabaseMiddleware::class);

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
