<?php

use App\Helpers\DateTimeJsonType;
use App\Entity\Repositories as EntityRepo;
use App\Repositories\Authorization as AuthRepo;
use App\Helpers;
use Defuse\Crypto\Key;
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

$c[EntityRepo\OrganismRepository::class] = function (Container $c) {
	return new EntityRepo\OrganismRepositoryImpl($c[EntityManager::class]);
};

$c[AuthRepo\ClientRepository::class] = function (Container $c) {
	return new AuthRepo\ClientRepository($c[EntityManager::class]);
};

$c[AuthRepo\UserRepository::class] = function (Container $c) {
	return new AuthRepo\UserRepository($c[EntityManager::class]);
};

$c[AuthRepo\ScopeRepository::class] = function (Container $c) {
	return new AuthRepo\ScopeRepository;
};

$c[AuthRepo\AccessTokenRepository::class] = function (Container $c) {
	return new AuthRepo\AccessTokenRepository($c[EntityManager::class]);
};

$c[AuthRepo\RefreshTokenRepository::class] = function(Container $c)
{
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

$c[EntityRepo\ExperimentEventRepository::class] = function (Container $c) {
    return new EntityRepo\ExperimentEventRepository($c[EntityManager::class]);
};

$c[EntityRepo\BioquantityRepository::class] = function (Container $c) {
    return new EntityRepo\BioquantityRepository($c[EntityManager::class]);
};

$c[EntityRepo\BioquantityMethodRepository::class] = function (Container $c) {
    return new EntityRepo\BioquantityMethodRepository($c[EntityManager::class]);
};

$c[EntityRepo\BioquantityVariableRepository::class] = function (Container $c) {
    return new EntityRepo\BioquantityVariableRepository($c[EntityManager::class]);
};

$c[EntityRepo\DeviceRepository::class] = function (Container $c) {
    return new EntityRepo\DeviceRepository($c[EntityManager::class]);
};

$c[EntityRepo\UnitRepository::class] = function (Container $c) {
    return new EntityRepo\UnitRepository($c[EntityManager::class]);
};

$c[EntityRepo\ProtocolRepository::class] = function (Container $c) {
    return new EntityRepo\ProtocolRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentEventTypeRepository::class] = function (Container $c) {
    return new EntityRepo\ExperimentEventTypeRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentEventVarTypeRepository::class] = function (Container $c) {
    return new EntityRepo\ExperimentEventVarTypeRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentEventArgRepository::class] = function (Container $c) {
    return new EntityRepo\ExperimentEventArgRepository($c[EntityManager::class]);
};

$c[EntityRepo\ExperimentEventResponseRepository::class] = function (Container $c) {
    return new EntityRepo\ExperimentEventResponseRepository($c[EntityManager::class]);
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

return $c;
