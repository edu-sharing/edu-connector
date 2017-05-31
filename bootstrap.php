<?php

use Monolog\Handler\RedisHandler;
use Monolog\Formatter\LogstashFormatter;
use Predis\Client;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$container = new \Slim\Container;
$app = new \Slim\App($container);

$container = $app->getContainer();
$container['log'] = function ($container) {
    $log = new \Monolog\Logger('eduConnector');
    $log->pushProcessor(new Monolog\Processor\IntrospectionProcessor());

    /*
     * Log to local file
     * */
    $log->pushHandler(new Monolog\Handler\RotatingFileHandler(__DIR__ . '/log/connector_error.log', 0, \Monolog\Logger::ERROR));
    $log->pushHandler(new Monolog\Handler\RotatingFileHandler(__DIR__ . '/log/connector_info.log', 0, \Monolog\Logger::INFO));

    /*
     * Log to redis/logstash
     */
    if(REDISSERVER && !empty(REDISSERVER)) {
        $redisHandler = new RedisHandler(new Client(REDISSERVER), 'phplogs');
        $formatter = new LogstashFormatter('eduConnector');
        $redisHandler->setFormatter($formatter);
        $log->pushHandler($redisHandler);
    }

    return $log;
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

$app->get('/ajax/unlockNode', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    try {
        $apiClient = new \connector\lib\EduRestClient();
        $apiClient->unlockNode($_SESSION['node']->node->ref->id);
    } catch (\Exception $e) {
        $response = $response->withStatus(500);
        $this->get('log')->error($e->getMessage());
    }
    return $response;
});

$app->post('/ajax/setText', function (Request $request, Response $response) {
    $this->get('log')->info($request->getUri());
    try {
        $apiClient = new \connector\lib\EduRestClient();
        $parsedBody = $request->getParsedBody();
        $content = $parsedBody['text'];
        $apiClient->createTextContent($_SESSION['node']->node->ref->id, $content, 'text/html', '');
    } catch (\Exception $e) {
        $response = $response->withStatus(500);
        $this->get('log')->error($e->getMessage());
    }
    return $response;
});

$app -> run();
