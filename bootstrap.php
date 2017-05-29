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
    $log->pushHandler(new Monolog\Handler\RotatingFileHandler(__DIR__ . '/log/connector.log', 0, \Monolog\Logger::DEBUG));

    /*
     * Log to redis/logstash
     */
    $redisHandler = new RedisHandler(new Client('tcp://78.46.71.7:6379'), 'phplogs');
    $formatter = new LogstashFormatter('eduConnector');
    $redisHandler->setFormatter($formatter);
    $log->pushHandler($redisHandler);

    $log->info('eduConnector bootstrapped');

    return $log;
};

$app->get('/', function (Request $request, Response $response) {
    $connector = new \connector\lib\Connector($this->get('log'));
});

$app->get('/metadata', function (Request $request, Response $response) {
    $metadataGenerator = new \connector\lib\MetadataGenerator();
    $metadataGenerator -> serve();
});

$app->get('/ajax/pingApi', function (Request $request, Response $response) {
    try {
        $apiClient = new \connector\lib\EduRestClient();
        $apiClient->validateSession();
        //$response->getBody()->write($sessionData);
    } catch (\Exception $e) {
        $response = $response->withStatus(500);
        $this->get('log')->error($e->getMessage());
    }
    return $response;
});

$app->get('/ajax/unlockNode', function (Request $request, Response $response) {
    try {
        $apiClient = new \connector\lib\EduRestClient();
        $apiClient->unlockNode();
    } catch (\Exception $e) {
        $response = $response->withStatus(500);
        $this->get('log')->error($e->getMessage());
    }
    return $response;
});

$app->post('/ajax/setText', function (Request $request, Response $response) {
    try {
        $apiClient = new \connector\lib\EduRestClient();
        $parsedBody = $request->getParsedBody();
        $content = $parsedBody->content;
        $apiClient->createTextContent($_SESSION['node']->ref->id, $content, 'text/html', '');
    } catch (\Exception $e) {
        $response = $response->withStatus(500);
        $this->get('log')->error($e->getMessage());
    }
    return $response;
});
