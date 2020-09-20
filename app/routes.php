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

		if ($this->mask & self::LIST) {
			$routes[] = $route = self::$app->get($this->path, $this->className . ':read');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}

		if ($this->mask & self::DETAIL) {
			$routes[] = $route = self::$app->get($this->path . '/{' . $idName . ':(?:\\d,?)+}', $this->className . ':readIdentified');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}

		if ($this->mask & self::ADD) {
			$routes[] = $route = self::$app->post($this->path, $this->className . ':add');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}

		if ($this->mask & self::EDIT) {
			$routes[] = $route = self::$app->put($this->path . '/{' . $idName . ':\\d+}', $this->className . ':edit');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}

		if ($this->mask & self::DELETE) {
			$routes[] = $route = self::$app->delete($this->path . '/{' . $idName . ':\\d+}', $this->className . ':delete');
			if ($this->authMask & self::LIST)
				$route->add(self::$authMiddleware);
		}
	}

}

return function(App $app) {
	RouteHelper::$app = $app;
	RouteHelper::$authMiddleware = new ResourceServerMiddleware($app->getContainer()[ResourceServer::class]);

	// main
	$app->get('/', function (Request $request, Response $response, Helpers\ArgumentParser $args) {
		return $response->withRedirect('/version');
	});

	// version
	$app->get('/version', Ctl\VersionController::class);

	// OAuth2.0
	$app->post('/authorize', Ctl\AuthorizeController::class);

	// User confirm registration
    $app->get('/users/{email}/{hash}', Ctl\UserController::class  . ':confirmRegistration');

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
	(new RouteHelper)
		->setRoute(Ctl\ModelCompartmentController::class, '/models/{model-id:\\d+}/compartments')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelSpecieController::class, '/models/{model-id:\\d+}/compartments/{compartment-id:\\d+}/species')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelReactionController::class, '/models/{model-id:\\d+}/reactions')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelFunctionController::class, '/models/{model-id:\\d+}/reactions/{reaction-id:\\d+}/functions')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelFunctionDefinitionController::class, '/models/{model-id:\\d+}/functionDefinitions')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ReactionParentedReactionItemController::class, '/models/{model-id:\\d+}/reactions/{reaction-id:\\d+}/reactionItems')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\SpecieParentedReactionItemController::class, '/models/{model-id:\\d+}/compartments/{compartment-id:\\d+}/species/{specie-id:\\d+}/reactionItems')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelConstraintController::class, '/models/{model-id:\\d+}/constraints')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelEventController::class, '/models/{model-id:\\d+}/events')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelEventAssignmentController::class, '/models/{model-id:\\d+}/events/{event-id:\\d+}/eventAssignments')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelUnitDefinitionController::class, '/models/{model-id:\\d+}/unitDefinitions')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelUnitController::class, '/units')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelInitialAssignmentController::class, '/models/{model-id:\\d+}/initialAssignments')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelParentedParameterController::class, '/models/{model-id:\\d+}/parameters')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ReactionItemParentedParameterController::class, '/models/{model-id:\\d+}/reactions/{reactionItem-id:\\d+}/parameters')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelParentedRuleController::class, '/models/{model-id:\\d+}/rules')
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
    (new RouteHelper())
        ->setRoute(Ctl\AnalysisTypeController::class, '/analysisTypes')
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
        ->register();
    (new RouteHelper())
        ->setRoute(Ctl\AnalysisToolController::class, '/analysisTools')
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
        ->register();
    (new RouteHelper())
        ->setRoute(Ctl\AnalysisMethodController::class, '/analysisMethods')
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
        ->register();
    (new RouteHelper())
        ->setRoute(Ctl\AnalysisSettingsController::class, '/analysisMethods/{meth-id:\\d+}/settings')
        ->register();
    (new RouteHelper())
        ->setRoute(Ctl\AnalysisDatasetController::class, '/models/{model-id:\\d+}/datasets')
        ->register();
    (new RouteHelper())
        ->setRoute(Ctl\AnalysisTaskController::class, '/{obj-type:experiment|model}s/{obj-id:\\d+}/tasks')
        ->register();
    (new RouteHelper())
        ->setRoute(Ctl\AnalysisTaskController::class, '/analysisTasks')
        ->register();
    (new RouteHelper())
        ->setRoute(Ctl\UserController::class, '/users')
        ->register();
	(new RouteHelper)
		->setRoute(Ctl\UserTypeController::class, '/userTypes')
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\UserGroupController::class, '/userGroups')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\UserGroupRoleController::class, '/userGroupRoles')
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
        ->register();

	// model species
	$app->get('/models/{model-id:\\d+}/species/{sbmlId}', Ctl\ModelSpecieController::class . ':readSbmlId');
	$app->get('/models/{model-id:\\d+}/parameters/{sbmlId}', Ctl\ModelParentedParameterController::class . ':readSbmlId');

	//experiment
    $app->delete('/experiment/{exp-id}/data',  Ctl\ExperimentController::class . ':deleteData');

	// entities
	$app->post('/entities/{id:\\d+}/status', Ctl\EntityController::class . ':editStatus');
	$app->get('/entities/{code}', Ctl\EntityController::class . ':readCode');

	// classifications
	$app->get('/classifications[/{type}]', Ctl\ClassificationController::class . ':read');
};
