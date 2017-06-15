<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$container = new \Slim\Container;
$app = new \Slim\App($container);

$container = $app->getContainer();
$container['log'] = function ($container) {
    $log = new \connector\lib\Logger();
    return $log->getLog();
};

$app->get('/', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $connector = new \connector\lib\Connector($this->get('log'));
});

$app->get('/metadata', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $metadataGenerator = new \connector\lib\MetadataGenerator();
    $metadataGenerator -> serve();
});

$app->get('/validateSession', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $metadataGenerator = new \connector\lib\MetadataGenerator();
    $metadataGenerator -> serve();
});

$app->get('/ajax/unlockNode', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $id = $request->getHeaderLine('X-CSRF-Token');
    try {
        $apiClient = new \connector\lib\EduRestClient($id);
        $apiClient->unlockNode($_SESSION[$id]['node']->node->ref->id);
    } catch (\Exception $e) {
        $response = $response->withStatus($e -> getCode());
        $this->get('log')->error('HTTP ' . $e -> getCode() . ' ' . $e->getMessage());
    }
    return $response;
});

$app->post('/ajax/setText', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    $id = $request->getHeaderLine('X-CSRF-Token');
    try {
        $apiClient = new \connector\lib\EduRestClient($id);
        $parsedBody = $request->getParsedBody();
        $content = $parsedBody['text'];
        $apiClient->createTextContent($_SESSION[$id]['node']->node->ref->id, $content, 'text/html', '');
    } catch (\Exception $e) {
        $response = $response->withStatus($e -> getCode());
        $this->get('log')->error('HTTP ' . $e -> getCode() . ' ' . $e->getMessage());
    }
    $_SESSION[$id]['content'] = $content;
    return $response;
});

$app -> run();
