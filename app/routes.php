<?php

use App\Controllers as Ctl;
use App\Helpers;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use League\OAuth2\Server\ResourceServer;
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

	/** @var League\OAuth2\Server\Middleware\ */
	public static $authMiddleware;

	/** @var string */
	private $path;

	/** @var string */
	private $className;

	/** @var int */
	private $mask = self::ALL;

	/** @var int */
	private $authMask = 0;

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

	public function setAuthMask(int $mask): RouteHelper
	{
		$this->authMask = $mask;
		return $this;
	}

	public function register(string $idName = 'id')
	{
		$routes = [];

		if ($this->mask & self::LIST)
		{
			$routes[] = $route = self::$app->get($this->path, $this->className . ':read');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}

		if ($this->mask & self::DETAIL)
		{
			$routes[] = $route = self::$app->get($this->path . '/{' . $idName . ':(?:\\d,?)+}', $this->className . ':readIdentified');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}

		if ($this->mask & self::ADD)
		{
			$routes[] = $route = self::$app->post($this->path, $this->className . ':add');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}

		if ($this->mask & self::EDIT)
		{
			$routes[] = $route = self::$app->put($this->path . '/{' . $idName . ':\\d+}', $this->className . ':edit');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}

		if ($this->mask & self::DELETE)
		{
			$routes[] = $route = self::$app->delete($this->path . '/{' . $idName . ':\\d+}', $this->className . ':delete');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}
	}
}

return function(App $app)
{
	RouteHelper::$app = $app;
	RouteHelper::$authMiddleware = new ResourceServerMiddleware($app->getContainer()[ResourceServer::class]);

	// main
	$app->get('/', function (Request $request, Response $response, Helpers\ArgumentParser $args)
	{
		return $response->withRedirect('/version');
	});

	// version
	$app->get('/version', Ctl\VersionController::class);
	$app->post('/authorize', Ctl\AuthorizeController::class);

	(new RouteHelper)
		->setRoute(Ctl\OrganismController::class, '/organisms')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ExperimentController::class, '/experiments')
		->register();
    (new RouteHelper)
        ->setRoute(Ctl\VariablesValuesController::class, '/experimentvalues')
        ->register();
	(new RouteHelper)
        ->setRoute(Ctl\ExperimentVariableController::class, '/experiments/{experiment-id:\\d+}/variables')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ExperimentValueController::class, '/experiments/{experiment-id:\\d+}/variables/{variable-id:\\d+}/values')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ExperimentNoteController::class, '/experiments/{experiment-id:\\d+}/notes')
		->register();
    (new RouteHelper)
        ->setRoute(Ctl\ExperimentVariableNoteController::class, '/experiments/{experiment-id:\\d+}/variables/{variable-id:\\d+}/notes')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\ExperimentEventController::class, '/experiments/{experiment-id:\\d+}/events')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\BioquantityController::class, '/bioquantities')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\BioquantityMethodController::class, '/bioquantities/{bioquantity-id:\\d+}/methods')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\BioquantityVariableController::class, '/bioquantities/{bioquantitiy-id:\\d+}/methods/{method-id:\\d+}/variables')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\DeviceController::class, '/devices')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\UnitController::class, '/units')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\ProtocolController::class, '/protocols')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\ExperimentEventTypeController::class, '/eventtypes')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\ExperimentEventVarTypeController::class, '/eventvartypes')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\ExperimentEventArgController::class, '/experiments/{experiment-id:\\d+}/events/{event-id:\\d+}/args')
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\ExperimentEventResponseController::class, '/experiments/{experiment-id:\\d+}/events/{event-id:\\d+}/responses')
        ->register();
};
