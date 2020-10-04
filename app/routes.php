<?php

use App\Controllers as Ctl;
use App\Helpers;
use App\Helpers\NonstrictResourceServerMiddleware;
use Doctrine\ORM\EntityManager;
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
	RouteHelper::$authMiddleware = new NonstrictResourceServerMiddleware($app->getContainer()[ResourceServer::class]);

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

    // Currently logged user routes
    $app->get('/user', Ctl\LoggedInUserController::class . ':readIdentified')
        //->add(new UserPermissionsControllerMiddleware($app->getContainer()[EntityManager::class]))
        ->add(RouteHelper::$authMiddleware);
    $app->put('/user', Ctl\LoggedInUserController::class . ':edit')
        ->add(RouteHelper::$authMiddleware);
    $app->delete('/user', Ctl\LoggedInUserController::class . ':delete')
        ->add(RouteHelper::$authMiddleware);

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

	// models module
	(new RouteHelper)
		->setRoute(Ctl\ModelController::class, '/models')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelCompartmentController::class, '/models/{model-id:\\d+}/compartments')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelSpecieController::class, '/models/{model-id:\\d+}/compartments/{compartment-id:\\d+}/species')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelReactionController::class, '/models/{model-id:\\d+}/reactions')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelFunctionController::class, '/models/{model-id:\\d+}/reactions/{reaction-id:\\d+}/functions')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelFunctionDefinitionController::class, '/models/{model-id:\\d+}/functionDefinitions')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ReactionParentedReactionItemController::class, '/models/{model-id:\\d+}/reactions/{reaction-id:\\d+}/reactionItems')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\SpecieParentedReactionItemController::class, '/models/{model-id:\\d+}/compartments/{compartment-id:\\d+}/species/{specie-id:\\d+}/reactionItems')
        ->setAuthMask(true)
        ->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelConstraintController::class, '/models/{model-id:\\d+}/constraints')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelEventController::class, '/models/{model-id:\\d+}/events')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelEventAssignmentController::class, '/models/{model-id:\\d+}/events/{event-id:\\d+}/eventAssignments')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelUnitDefinitionController::class, '/models/{model-id:\\d+}/unitDefinitions')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelUnitController::class, '/units')
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelInitialAssignmentController::class, '/models/{model-id:\\d+}/initialAssignments')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelParentedParameterController::class, '/models/{model-id:\\d+}/parameters')
        ->setAuthMask(true)
		->register();
	#FIXME ------ WTH is this endpoint? Makes no sense
	(new RouteHelper)
		->setRoute(Ctl\ReactionItemParentedParameterController::class, '/models/{model-id:\\d+}/reactions/{reactionItem-id:\\d+}/parameters')
        ->setAuthMask(true)
		->register();
	// ------------
	(new RouteHelper)
		->setRoute(Ctl\ModelParentedRuleController::class, '/models/{model-id:\\d+}/rules')
        ->setAuthMask(true)
		->register();

	// experiments module
	(new RouteHelper)
		->setRoute(Ctl\ExperimentController::class, '/experiments')
        ->setAuthMask(true)
		->register();
    (new RouteHelper)
        ->setRoute(Ctl\VariablesValuesController::class, '/experimentvalues')
        ->setAuthMask(true)
        ->register();
	(new RouteHelper)
        ->setRoute(Ctl\ExperimentVariableController::class, '/experiments/{experiment-id:\\d+}/variables')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ExperimentValueController::class, '/experiments/{experiment-id:\\d+}/variables/{variable-id:\\d+}/values')
        ->setAuthMask(true)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ExperimentNoteController::class, '/experiments/{experiment-id:\\d+}/notes')
        ->setAuthMask(true)
		->register();
    (new RouteHelper)
        ->setRoute(Ctl\ExperimentVariableNoteController::class, '/experiments/{experiment-id:\\d+}/variables/{variable-id:\\d+}/notes')
        ->setAuthMask(true)
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\DeviceController::class, '/devices')
        ->register();

    //analysis module
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
        ->setAuthMask(true)
        ->register();
    (new RouteHelper())
        ->setRoute(Ctl\AnalysisTaskController::class, '/{obj-type:experiment|model}s/{obj-id:\\d+}/tasks')
        ->setAuthMask(true)
        ->register();
    (new RouteHelper())
        ->setRoute(Ctl\AnalysisTaskController::class, '/analysisTasks')
        ->setAuthMask(true)
        ->register();

    // authorize module
    (new RouteHelper())
        ->setRoute(Ctl\UserController::class, '/users')
        ->setAuthMask(true)
        ->register();
	(new RouteHelper)
		->setRoute(Ctl\UserTypeController::class, '/userTypes')
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\UserGroupController::class, '/userGroups')
        ->setAuthMask(true)
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
