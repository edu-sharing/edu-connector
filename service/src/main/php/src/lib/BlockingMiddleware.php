<?php declare(strict_types=1);

namespace connector\lib;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class BlockingMiddleware
 *
 * includes logic to block the connector service for the
 * duration of the cache cleaning process.
 * This is needed in order to prevent potential invalid state creation.
 *
 * @author Marian Ziegler <ziegler@edu-sharing.net>
 */
class BlockingMiddleware
{
    /**
     * Function __invoke
     *
     * Function to run the middleware as per the Slim PHP documentation
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $retryCount = 0;
        while (file_exists(__DIR__ . '/' . CacheCleaner::LOCK_FILE_NAME) && $retryCount <= 10) {
            sleep(1);
            $retryCount += 1;
        }
        if (file_exists(__DIR__ . '/' . CacheCleaner::LOCK_FILE_NAME)) {
            return $response->withStatus(503);
        }
        return $next($request, $response);
    }
}
