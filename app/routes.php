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

	$addRwController = function(string $className, string $path, string $idName = 'id') use ($app, $multiIdRegex)
	{
		$app->get($path, $className . ':read');
		$app->get($path . '/{' . $idName . ':(?:\\d,?)+}', $className . ':readIdentified');
		$app->post($path, $className . ':add');
		$app->put($path . '/{' . $idName . ':\\d+}', $className . ':edit');
		$app->delete($path . '/{' . $idName . ':\\d+}', $className . ':delete');
	};

	// version
	$app->get('/version', Ctl\VersionController::class);

	$addRwController(Ctl\EntityController::class, '/entities');
	$addRwController(Ctl\EntityAnnotationsController::class, '/entities/{entity-id:\\d+}/annotations');
	$addRwController(Ctl\EntityNoteController::class, '/entities/{entity-id:\\d+}/notes');
	$addRwController(Ctl\RuleController::class, '/rules');
	$addRwController(Ctl\RuleAnnotationsController::class, '/rules/{rule-id:\\d+}/annotations');
	$addRwController(Ctl\RuleNoteController::class, '/rules/{rule-id:\\d+}/notes');

	// entities
	$app->post('/entities/{id:\\d+}/status', Ctl\EntityController::class . ':editStatus');
	$app->get('/entities/{code}', Ctl\EntityController::class . ':readCode');

	// organisms
	$app->get('/organisms/' . $multiIdRegex, Ctl\OrganismController::class . ':readIdentified');
	$app->get('/organisms', Ctl\OrganismController::class . ':read');

	// Classifications
	$app->get('/classifications/' . $multiIdRegex, Ctl\ClassificationController::class . ':readIdentified');
	$app->get('/classifications[/{type}]', Ctl\ClassificationController::class . ':read');
};
