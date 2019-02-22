<?php

use App\Helpers\DateTimeJsonType;
use App\Entity\Repositories as EntityRepo;
use App\Helpers;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Types\Type;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

$config = require __DIR__ . '/../app/settings.php';

Type::overrideType('datetime', DateTimeJsonType::class);
Type::overrideType('datetimetz', DateTimeJsonType::class);

$c = new Container($config);
unset($c['errorHandler']);
unset($c['phpErrorHandler']);
unset($c['view']);
unset($c['logger']);

\Tracy\Debugger::enable($c->settings['tracy']['mode'], $c->settings['tracy']['logDir']);
\Tracy\Debugger::timer('execution');
//\Tracy\Debugger::$onFatalError[] = function(\Throwable $exception)
//{
//	header('Content-type: application/json');
//	echo json_encode([
//		'status' => 'error',
//		'code' => 500,
//		'message' => '',
//	]);
//};

$c['persistentData'] = function (Container $c) {
	return (object)['needsFlush' => false];
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

	return EntityManager::create($settings['doctrine']['connection'], $config);
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

$c[EntityRepo\ModelFunctionRepository::class] = function (Container $c) {
	return new EntityRepo\ModelFunctionRepository($c[EntityManager::class]);
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

$c[EntityRepo\ModelUnitDefinitionRepository::class] = function (Container $c) {
	return new EntityRepo\ModelUnitDefinitionRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelUnitRepository::class] = function (Container $c) {
	return new EntityRepo\ModelUnitRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelInitialAssignmentRepository::class] = function (Container $c) {
	return new EntityRepo\ModelInitialAssignmentRepository($c[EntityManager::class]);
};

$c[EntityRepo\ModelParameterRepository::class] = function (Container $c) {
	return new EntityRepo\ModelParameterRepository($c[EntityManager::class]);
};

return $c;
