<?php


namespace App\Helpers;

use App\Exceptions\InternalErrorException;
use App\Exceptions\InvalidAuthenticationException;
use Exception;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class NonstrictResourceServerMiddleware is just League\OAuth2\Server\Middleware\ResourceServerMiddleware
 * adjusted to our need.
 * @package App\Helpers
 */
class NonstrictResourceServerMiddleware
{
    /**
     * @var ResourceServer
     */
    private $server;

    /**
     * @param ResourceServer $server
     */
    public function __construct(ResourceServer $server)
    {
        $this->server = $server;
    }

    /**
     * @param ServerRequestInterface    $request
     * @param ResponseInterface         $response
     * @param callable                  $next
     *
     * @return ResponseInterface
     * @throws InternalErrorException
     * @throws InvalidAuthenticationException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($request->getHeader('http_authorization')) {
             try {
                 $request = $this->server->validateAuthenticatedRequest($request);
             } catch (OAuthServerException $exception) {
                 throw new InvalidAuthenticationException($exception->getMessage(), $exception->getHint());
                 // @codeCoverageIgnoreStart
             } catch (Exception $exception) {
                 throw new InternalErrorException($exception->getMessage(), $exception);
                 // @codeCoverageIgnoreEnd
             }
        }

        // Pass the request and response on to the next responder in the chain
        return $next($request, $response);
    }
}