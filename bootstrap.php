<?php

use Monolog\Handler\RedisHandler;
use Monolog\Formatter\LogstashFormatter;
use Predis\Client;


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
