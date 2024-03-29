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

	public function addAnnotationsRoutes(): RouteHelper
    {
        (new RouteHelper())
            ->setRoute(Ctl\AnnotationSourceController::class, $this->path . '/{obj-id:\\d+}/annotations')
            ->setAuthMask($this->authMask)
            ->setMask(RouteHelper::ADD | RouteHelper::EDIT | RouteHelper::DELETE)
            ->register();
        return $this;
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
    $app->put('/user/resendConfirmation', Ctl\LoggedInUserController::class . ':resendCnfEmail')
        ->add(RouteHelper::$authMiddleware);
    $app->post('/users/passwordRenewal', Ctl\LoggedInUserController::class . ':getNewPsw');
    $app->put('/users/{email}/pswRenew/{hash}', Ctl\LoggedInUserController::class . ':generateNewPsw');


	// annotations
	$app->get('/annotations/types', Ctl\AnnotationController::class . ':readTypes');
	$app->get('/annotations/link/{type}', Ctl\AnnotationController::class . ':readLink');


	(new RouteHelper)
		->setRoute(Ctl\ClassificationController::class, '/classifications')
		->setMask(RouteHelper::ALL & ~RouteHelper::LIST)
		->register();
	(new RouteHelper)
		->setRoute(Ctl\OrganismController::class, '/organisms')
        ->setAuthMask(true)
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
		->setRoute(Ctl\ModelController::class, '/{obj:model}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelCompartmentController::class, '/models/{model-id:\\d+}/{obj:compartment}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelSpecieController::class,
            '/models/{model-id:\\d+}/compartments/{compartment-id:\\d+}/{obj:specie}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelReactionController::class, '/models/{model-id:\\d+}/{obj:reaction}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelFunctionDefinitionController::class,
            '/models/{model-id:\\d+}/{obj:functionDefinition}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ReactionParentedReactionItemController::class,
            '/models/{model-id:\\d+}/reactions/{reaction-id:\\d+}/{obj:reactionItem}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\SpecieParentedReactionItemController::class,
            '/models/{model-id:\\d+}/compartments/{compartment-id:\\d+}/species/{specie-id:\\d+}/{obj:reactionItem}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
        ->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelConstraintController::class, '/models/{model-id:\\d+}/{obj:constraint}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelEventController::class, '/models/{model-id:\\d+}/{obj:event}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelEventAssignmentController::class,
            '/models/{model-id:\\d+}/events/{event-id:\\d+}/{obj:eventAssignment}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelInitialAssignmentController::class,
            '/models/{model-id:\\d+}/{obj:initialAssignment}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	(new RouteHelper)
		->setRoute(Ctl\ModelParentedParameterController::class, '/models/{model-id:\\d+}/{obj:parameter}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
	#FIXME ------ WTH is this endpoint? Makes no sense
	// Note: This is an endpoint for parameters bound to a specific reaction, as opposed to global parameters - Havlík
    #FIXME ----- the implementation of the class is getting the data from reaction of some ID, rather then reaction-item
    # of some id. According to your comment, this looks like it is your historical error and therefore needs fixing.
	(new RouteHelper)
		->setRoute(Ctl\ReactionItemParentedParameterController::class, '/models/{model-id:\\d+}/reactions/{reactionItem-id:\\d+}/parameters')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();
    (new RouteHelper())
        ->setRoute(Ctl\ModelDatasetController::class, '/models/{model-id:\\d+}/datasets')
        ->setAuthMask(true)
        ->register();
	// ------------
	(new RouteHelper)
		->setRoute(Ctl\ModelParentedRuleController::class, '/models/{model-id:\\d+}/{obj:rule}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();

    (new RouteHelper)
        ->setRoute(Ctl\ImportModelController::class,'/models/import')
        ->setAuthMask(true)
        ->setMask(RouteHelper::ADD)
        ->register();

    $app->get('/models/{id:\\d+}/SBML', Ctl\ModelController::class . ':getSBML')
        ->add(RouteHelper::$authMiddleware);

    $app->post('/models/{id:\\d+}/SBML', Ctl\ModelController::class . ':getSBML')
        ->add(RouteHelper::$authMiddleware);


	// experiments module
	(new RouteHelper)
		->setRoute(Ctl\ExperimentController::class, '/{obj:experiment}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
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
        ->setRoute(Ctl\ExperimentGraphsetController::class, '/experiments/{experiment-id:\\d+}/graphsets')
        ->setMask(RouteHelper::ADD | RouteHelper::EDIT | RouteHelper::DELETE)
        ->setAuthMask(true)
        ->register();

    (new RouteHelper)
        ->setRoute(Ctl\DeviceController::class, '/devices')
        ->addAnnotationsRoutes()
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
    // Group management
    $app->post('/userGroups/{id:\\d+}/addUsers', Ctl\UserGroupController::class . ':addUsers')
        ->add(RouteHelper::$authMiddleware);
	(new RouteHelper)
		->setRoute(Ctl\UserGroupRoleController::class, '/userGroupRoles')
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\NotificationLogController::class, '/notificationLog')
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
        //->setAuthMask(true)
        ->register();

	// Bioquantities module
	(new RouteHelper)
		->setRoute(Ctl\BioquantityController::class, '/{obj:bioquantitie}s')
        ->setAuthMask(true)
        ->addAnnotationsRoutes()
		->register();

	// Units module
    (new RouteHelper)
        ->setRoute(Ctl\PhysicalQuantityController::class, '/physicalQuantities')
        ->setAuthMask(true)
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\UnitController::class, '/physicalQuantities/{physicalQuantity-id:\\d+}/units')
        ->setAuthMask(true)
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\UnitAliasController::class, '/physicalQuantities/{physicalQuantity-id:\\d+}/units/{unit-id:\\d+}/aliases')
        ->setAuthMask(true)
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\AttributeController::class, '/physicalQuantities/{physicalQuantity-id:\\d+}/attributes')
        ->setAuthMask(true)
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\PhysicalQuantityHierarchyController::class, '/physicalQuantities/{physicalQuantity-id:\\d+}/hierarchy')
        ->setAuthMask(true)
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\UnitsAllController::class, '/unitsall')
        ->setAuthMask(true)
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
        ->register();
    (new RouteHelper)
        ->setRoute(Ctl\UnitsAliasesAllController::class, '/unitsAliasesAll')
        ->setAuthMask(true)
        ->setMask(RouteHelper::LIST | RouteHelper::DETAIL)
        ->register();

    //Annotation
    (new RouteHelper())
        ->setRoute(Ctl\AnnotationController::class,
            '/{.+/}{obj-type:
            experiments|models}/{obj-id:\\d+}/annotations')
        ->setAuthMask(true)
        ->setMask(RouteHelper::ADD | RouteHelper::EDIT | RouteHelper::DELETE)
        ->register();

	// model species
	$app->get('/models/{model-id:\\d+}/species/{sbmlId}', Ctl\ModelSpecieController::class . ':readSbmlId');
	$app->get('/models/{model-id:\\d+}/parameters/{sbmlId}', Ctl\ModelParentedParameterController::class . ':readSbmlId');

	//experiment
    $app->delete('/experiment/{exp-id}/data',  Ctl\ExperimentController::class . ':deleteData');
    $app->post('/experiment/values', Ctl\ExperimentValueController::class . ':createObjects');

	// entities
	$app->post('/entities/{id:\\d+}/status', Ctl\EntityController::class . ':editStatus');
	$app->get('/entities/{code}', Ctl\EntityController::class . ':readCode');

	// classifications
	$app->get('/classifications[/{type}]', Ctl\ClassificationController::class . ':read');

};
