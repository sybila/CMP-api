<?php

use App\Entity\AnnotableObjectType;
use App\Entity\Authorization\Notification\MailNotification;
use App\Helpers\DateTimeJsonType;
use App\Entity\Repositories as EntityRepo;
use App\Repositories\Authorization as AuthRepo;
use App\Helpers;
use Defuse\Crypto\Key;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Types\Type;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

$config = require __DIR__ . '/../app/settings.php';

Type::overrideType('datetime', DateTimeJsonType::class);
Type::overrideType('datetime_immutable', DateTimeJsonType::class);
Type::overrideType('datetime', DateTimeJsonType::class);
Type::addType('annotable_obj_type',AnnotableObjectType::class);

$c = new Container($config);
unset($c['errorHandler']);
unset($c['phpErrorHandler']);
unset($c['view']);
unset($c['logger']);

\Tracy\Debugger::enable($c->settings['tracy']['mode'], $c->settings['tracy']['logDir']);
\Tracy\Debugger::timer('execution');
/* \Tracy\Debugger::$onFatalError[] = function(\Throwable $exception)
  {
  header('Content-type: application/json');
  echo json_encode([
  'status' => 'error',
  'code' => 500,
  'message' => '',
  ]);
  }; */

$c['persistentData'] = function (Container $c) {
	return (object) ['needsFlush' => false];
};

// Doctrine
$c[EntityManager::class] = function (Container $c) {
	$settings = $c->settings;
	$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
			$settings['doctrine']['meta']['entity_path'],
			$settings['doctrine']['meta']['auto_generate_proxies'],
			$settings['doctrine']['meta']['proxy_dir'],
			$settings['doctrine']['meta']['cache'],
			false
	);

	$config->addCustomStringFunction('TYPE', \App\Doctrine\ORM\Query\Functions\TypeFunction::class);

//    $eventManager = new EventManager();
//    $eventManager->addEventSubscriber(
//        new \App\Controllers\NotificationDispatchController()
//    );

	return EntityManager::create($settings['doctrine']['connection'], $config);
        //, $eventManager);
};

$c['foundHandler'] = function (Container $c) {
	return new Helpers\RequestResponseParsedArgs;
};

$c['notFoundHandler'] = function (Container $c) {
	return function (Request $request, Response $response) {
		return $response->withStatus(404)->withJson([
				'status' => 'error',
				'message' => 'Page not found',
				'code' => 404,
		]);
	};
};

$c['notAllowedHandler'] = function (Container $c) {
	return function (Request $request, Response $response, array $allowedHttpMethods) {
		return $response->withStatus(405)->withJson([
				'status' => 'error',
				'code' => 405,
				'message' => 'Allowed methods: ' . implode(', ', $allowedHttpMethods),
				'methods' => $allowedHttpMethods,
		]);
	};
};

$c['errorHandler'] = function (Container $c) {
	return function (Request $request, Response $response, \Throwable $exception) {
		if ($exception instanceof \App\Exceptions\ApiException)
			return $response->withStatus($exception->getHttpCode())->withJson([
					'status' => 'error',
					'code' => $exception->getCode(),
					'message' => $exception->getMessage(),
					] + $exception->getAdditionalData());

		if (!\Tracy\Debugger::$productionMode)
			throw $exception;

		\Tracy\Debugger::log($exception);
		return $response->withStatus(500)->withJson([
				'status' => 'error',
				'code' => 500,
				'message' => '',
		]);
	};
};

$c[EntityRepo\ClassificationRepository::class] = function (Container $c) {
	return new EntityRepo\ClassificationRepositoryImpl($c[EntityManager::class]);
};

$c[EntityRepo\OrganismRepository::class] = function (Container $c) {
	return new EntityRepo\OrganismRepositoryImpl($c[EntityManager::class]);
};

$c[EntityRepo\EntityRepository::class] = function (Container $c) {
	return new EntityRepo\EntityRepositoryImpl($c[EntityManager::class]);
};

$c[EntityRepo\RuleRepository::class] = function (Container $c) {
	return new EntityRepo\RuleRepositoryImpl($c[EntityManager::class]);
};

$c[EntityRepo\EntityAnnotationRepositoryImpl::class] = function (Container $c) {
	return new EntityRepo\EntityAnnotationRepositoryImpl($c[EntityManager::class]);
};

$c[EntityRepo\RuleAnnotationRepositoryImpl::class] = function (Container $c) {
	return new EntityRepo\RuleAnnotationRepositoryImpl($c[EntityManager::class]);
};

$c[EntityRepo\EntityNoteRepository::class] = function (Container $c) {
	return new EntityRepo\EntityNoteRepository($c[EntityManager::class]);
};

$c[EntityRepo\RuleNoteRepository::class] = function (Container $c) {
	return new EntityRepo\RuleNoteRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelRepository::class] = function (Container $c) {
	return new EntityRepo\ModelRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelCompartmentRepository::class] = function (Container $c) {
	return new EntityRepo\ModelCompartmentRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelCompartmentRepository::class] = function (Container $c) {
	return new EntityRepo\ModelCompartmentRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelSpecieRepository::class] = function (Container $c) {
	return new EntityRepo\ModelSpecieRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelRuleRepository::class] = function (Container $c) {
	return new EntityRepo\ModelRuleRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelReactionRepository::class] = function (Container $c) {
	return new EntityRepo\ModelReactionRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelFunctionDefinitionRepository::class] = function (Container $c) {
	return new EntityRepo\ModelFunctionDefinitionRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelReactionItemRepository::class] = function (Container $c) {
	return new EntityRepo\ModelReactionItemRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelConstraintRepository::class] = function (Container $c) {
	return new EntityRepo\ModelConstraintRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelEventRepository::class] = function (Container $c) {
	return new EntityRepo\ModelEventRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelEventAssignmentRepository::class] = function (Container $c) {
	return new EntityRepo\ModelEventAssignmentRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelInitialAssignmentRepository::class] = function (Container $c) {
	return new EntityRepo\ModelInitialAssignmentRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelParameterRepository::class] = function (Container $c) {
	return new EntityRepo\ModelParameterRepository($c[EntityManager::class]);
};

$c[AuthRepo\ClientRepository::class] = function (Container $c) {
	return new AuthRepo\ClientRepository($c[EntityManager::class]);
};

$c[AuthRepo\UserRepository::class] = function (Container $c) {
	return new AuthRepo\UserRepository($c[EntityManager::class]);
};

$c[AuthRepo\UserTypeRepository::class] = function (Container $c) {
	return new AuthRepo\UserTypeRepository($c[EntityManager::class]);
};

$c[AuthRepo\UserGroupRepository::class] = function (Container $c) {
	return new AuthRepo\UserGroupRepository($c[EntityManager::class]);
};

$c[AuthRepo\UserGroupRoleRepository::class] = function (Container $c) {
	return new AuthRepo\UserGroupRoleRepository($c[EntityManager::class]);
};

$c[AuthRepo\ScopeRepository::class] = function (Container $c) {
	return new AuthRepo\ScopeRepository;
};

$c[AuthRepo\AccessTokenRepository::class] = function (Container $c) {
	return new AuthRepo\AccessTokenRepository($c[EntityManager::class]);
};

$c[AuthRepo\RefreshTokenRepository::class] = function(Container $c) {
	return new AuthRepo\RefreshTokenRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentRepository::class] = function (Container $c) {
	return new EntityRepo\ExperimentRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentVariableRepository::class] = function (Container $c) {
	return new EntityRepo\ExperimentVariableRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentValueRepository::class] = function (Container $c) {
	return new EntityRepo\ExperimentValueRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentNoteRepository::class] = function (Container $c) {
	return new EntityRepo\ExperimentNoteRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentVariableNoteRepository::class] = function (Container $c) {
	return new EntityRepo\ExperimentVariableNoteRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentGraphsetRepository::class] = function (Container $c) {
    return new EntityRepo\ExperimentGraphsetRepository($c[EntityManager::class]);
};

$c[EntityRepo\DeviceRepository::class] = function (Container $c) {
	return new EntityRepo\DeviceRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelDatasetRepository::class] = function (Container $c) {
	return new EntityRepo\ModelDatasetRepository($c[EntityManager::class]);
};

$c[EntityRepo\PhysicalQuantityRepository::class] = function (Container $c) {
	return new EntityRepo\PhysicalQuantityRepository($c[EntityManager::class]);
};

$c[EntityRepo\UnitRepository::class] = function (Container $c) {
	return new EntityRepo\UnitRepository($c[EntityManager::class]);
};

$c[EntityRepo\UnitAliasRepository::class] = function (Container $c) {
	return new EntityRepo\UnitAliasRepository($c[EntityManager::class]);
};

$c[EntityRepo\BioquantityRepository::class] = function (Container $c) {
	return new EntityRepo\BioquantityRepository($c[EntityManager::class]);
};

$c[EntityRepo\PhysicalQuantityHierarchyRepository::class] = function (Container $c) {
	return new EntityRepo\PhysicalQuantityHierarchyRepository($c[EntityManager::class]);
};

$c[EntityRepo\AttributeRepository::class] = function (Container $c) {
	return new EntityRepo\AttributeRepository($c[EntityManager::class]);
};

$c[EntityRepo\UnitsAllRepository::class] = function (Container $c) {
	return new EntityRepo\UnitsAllRepository($c[EntityManager::class]);
};

$c[EntityRepo\UnitsAliasesAllRepository::class] = function (Container $c) {
	return new EntityRepo\UnitsAliasesAllRepository($c[EntityManager::class]);
};

$c[EntityRepo\AnnotationSourceRepository::class] = function (Container $c) {
    return new EntityRepo\AnnotationSourceRepository($c[EntityManager::class]);
};


$c[AuthorizationServer::class] = function (Container $c) {
	$srv = new AuthorizationServer(
		$c[AuthRepo\ClientRepository::class],
		$c[AuthRepo\AccessTokenRepository::class],
		$c[AuthRepo\ScopeRepository::class],
		$c->settings['oauth']['privateKey'],
		Key::loadFromAsciiSafeString($c->settings['oauth']['encryptionKey'])
	);

	$srv->enableGrantType(new RefreshTokenGrant(
			$c[AuthRepo\RefreshTokenRepository::class]
	));

	$srv->enableGrantType(new PasswordGrant(
			$c[AuthRepo\UserRepository::class],
			$c[AuthRepo\RefreshTokenRepository::class]
	));

	$srv->enableGrantType(new ClientCredentialsGrant);

	return $srv;
};

$c[ResourceServer::class] = function (Container $c) {
	return new ResourceServer(
		$c[AuthRepo\AccessTokenRepository::class],
		$c->settings['oauth']['publicKey']
	);
};

$c[AuthRepo\NotificationLogRepository::class] = function (Container $c) {
    return new AuthRepo\NotificationLogRepository($c[EntityManager::class]);
};

$c[MailNotification::class] = function (Container $c) {
    $mailerInfo = $c->settings['notifications']['mailer'];
    return new MailNotification(
        $mailerInfo['dsn'],
        $mailerInfo['salt'],
        $mailerInfo['client_srv_redirect']
    );
};

$c['notifications'] = $c->settings['notifications'];
return $c;
