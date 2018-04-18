<?php

use App\Controllers as Ctl;
use App\Helpers;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function(App $app)
{
	$multiIdRegex = '{id:(?:\\d,?)+}';

	// main
	$app->get('/', function (Request $request, Response $response, Helpers\ArgumentParser $args)
	{
		return $response->withRedirect('/version');
	});

	// version
	$app->get('/version', Ctl\VersionController::class);

	// entities
	$app->get('/entities', Ctl\EntityController::class . ':read');
	$app->get('/entities/' . $multiIdRegex, Ctl\EntityController::class . ':readIdentified');
	$app->post('/entities', Ctl\EntityController::class . ':add');
	$app->put('/entities/{id:\\d+}', Ctl\EntityController::class . ':edit');
	$app->post('/entities/{id:\\d+}/status', Ctl\EntityController::class . ':editStatus');
	$app->delete('/entities/{id:\\d+}', Ctl\EntityController::class . ':delete');
	$app->get('/entities/{code}', Ctl\EntityController::class . ':readCode');

	// rules
	$app->get('/rules', Ctl\RuleController::class . ':read');
	$app->get('/rules/' . $multiIdRegex, Ctl\RuleController::class . ':readIdentified');
	//$app->post('/rules', C\RuleController::class . ':add');
	//$app->put('/rules/{id:\\d+}', C\RuleController::class . ':edit');
	//$app->delete('/rules/{id:\\d+}', C\RuleController::class . ':delete');

	// organisms
	$app->get('/organisms/' . $multiIdRegex, Ctl\OrganismController::class . ':readIdentified');
	$app->get('/organisms', Ctl\OrganismController::class . ':read');

	// Classifications
	$app->get('/classifications/' . $multiIdRegex, Ctl\ClassificationController::class . ':readIdentified');
	$app->get('/classifications[/{type}]', Ctl\ClassificationController::class . ':read');
};
