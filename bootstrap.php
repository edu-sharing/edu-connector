<?php


/*
 * Log to local file
 * */
$log = new \Monolog\Logger();
$log->pushHandler(new Monolog\Handler\RotatingFileHandler(__DIR__ . '/log/connector.log', 0, \Monolog\Logger::DEBUG));
$log->pushProcessor(new Monolog\Processor\IntrospectionProcessor());


/*
 * Log to logstash
 */
use Monolog\Logger;
use Monolog\Handler\RedisHandler;
use Monolog\Formatter\LogstashFormatter;
use Predis\Client;


$redisHandler = new RedisHandler(new Client('tcp://78.46.71.7:6379'), 'phplogs');
$formatter = new LogstashFormatter('eduConnector');
$redisHandler->setFormatter($formatter);
$logger = new Logger('eduConnector', array($redisHandler));

// test
$logger->info('eduConnector bootstraped.');

$logger->error('eduConnector test an error.');