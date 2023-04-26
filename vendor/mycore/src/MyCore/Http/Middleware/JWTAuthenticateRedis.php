<?php
namespace MyCore\Http\Middleware;

use MyCore\Storage\Redis\AuthJwtStorageManager;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Closure;

/**
 * Class JWTAuthenticateRedis
 * @package MyCore\Http\Middleware
 * @author DaiDP
 * @since Aug, 2019
 */
class JWTAuthenticateRedis extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->authenticate($request);

        // Check token in redis db
        $this->validTokenRedis();

        $response = $next($request);

        // Send the refreshed token back to the client.
        $response = $this->setAuthenticationHeader($response);

        // add refresh token to queue
        $this->pushTokenRedis();

        return $response;
    }

    /**
     * Check token in redis queue
     */
    protected function validTokenRedis()
    {
        $oJwtStore = app(AuthJwtStorageManager::class);
        $rs = $oJwtStore->checkToken($this->auth->getToken()->get());

        if ($rs) {
            return;
        }

        throw new UnauthorizedHttpException('jwt-auth', 'Token expired');
    }

    /**
     * Storage token in redis queue
     */
    protected function pushTokenRedis()
    {
        $oJwtStore = app(AuthJwtStorageManager::class);
        $oJwtStore->saveToken($this->newToken);
    }

    /**
     * Set the authentication header.
     *
     * @param  \Illuminate\Http\Response|\Illuminate\Http\JsonResponse  $response
     * @param  string|null  $token
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function setAuthenticationHeader($response, $token = null)
    {
        $token = $token ?: $this->auth->refresh();

        $response->headers->set('Auth-Token', 'Bearer '.$token);
        $this->newToken = $token;

        return $response;
    }
}