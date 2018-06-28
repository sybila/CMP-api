<?php

use App\Controllers as Ctl;
use App\Helpers;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

class RouteHelper
{
	const LIST = 0x01;
	const DETAIL = 0x02;
	const ADD = 0x04;
	const EDIT = 0x08;
	const DELETE = 0x10;

	const ALL = self::LIST | self::DETAIL | self::ADD | self::EDIT | self::DELETE;

	/** @var App */
	public static $app;

	/** @var string */
	private $path;

	/** @var string */
	private $className;

	/** @var int */
	private $mask = self::ALL;

	public function setRoute(string $className, string $path): RouteHelper
	{
		$this->className = $className;
		$this->path = $path;
		return $this;
	}

	public function setMask(int $mask): RouteHelper
	{
		$this->mask = $mask;
		return $this;
	}

	public function register(string $idName = 'id')
	{
		if ($this->mask & self::LIST)
			self::$app->get($this->path, $this->className . ':read');

		if ($this->mask & self::DETAIL)
			self::$app->get($this->path . '/{' . $idName . ':(?:\\d,?)+}', $this->className . ':readIdentified');

		if ($this->mask & self::ADD)
			self::$app->post($this->path, $this->className . ':add');

		if ($this->mask & self::EDIT)
			self::$app->put($this->path . '/{' . $idName . ':\\d+}', $this->className . ':edit');

		if ($this->mask & self::DELETE)
			self::$app->delete($this->path . '/{' . $idName . ':\\d+}', $this->className . ':delete');
	}
}

return function(App $app)
{
	RouteHelper::$app = $app;

	// main
	$app->get('/', function (Request $request, Response $response, Helpers\ArgumentParser $args)
	{
		return $response->withRedirect('/version');
	});

	// version
	$app->get('/version', Ctl\VersionController::class);

	// annotations
	$app->get('/annotations/types', Ctl\AnnotationController::class . ':readTypes');
	$app->get('/annotations/link/{type}', Ctl\AnnotationController::class . ':readLink');

	(new RouteHelper)
		->setRoute(Ctl\ClassificationController::class, '/classifications')
		->setMask(RouteHelper::ALL & ~RouteHelper::LIST)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\OrganismController::class, '/organisms')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\EntityController::class, '/entities')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\EntityBcsAnnotationsController::class, '/entities/{entity-id:\\d+}/annotations')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\EntityNoteController::class, '/entities/{entity-id:\\d+}/notes')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\RuleController::class, '/rules')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\RuleBcsAnnotationsController::class, '/rules/{rule-id:\\d+}/annotations')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\RuleNoteController::class, '/rules/{rule-id:\\d+}/notes')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelController::class, '/models')
		->register();

	// entities
	$app->post('/entities/{id:\\d+}/status', Ctl\EntityController::class . ':editStatus');
	$app->get('/entities/{code}', Ctl\EntityController::class . ':readCode');

	// classifications
	$app->get('/classifications[/{type}]', Ctl\ClassificationController::class . ':read');
};
