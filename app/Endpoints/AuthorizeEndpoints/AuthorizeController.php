<?php

namespace App\Controllers;

use App\Helpers\ArgumentParser;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthorizeController extends AbstractController
{
	/** @var EntityManager */
	private $erm;

	/** @var AuthorizationServer */
	private $server;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->erm = $c[EntityManager::class];
		$this->server = $c[AuthorizationServer::class];
	}

	public function __invoke(Request $request, Response $response, ArgumentParser $args)
	{
		try {
            $response = $this->server->respondToAccessTokenRequest($request, $response);
			$this->erm->flush();
			return $response;
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
	}
}
