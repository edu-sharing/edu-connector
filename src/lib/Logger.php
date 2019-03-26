<?php

namespace connector\lib;

use Monolog\Handler\RedisHandler;
use Monolog\Formatter\LogstashFormatter;
use Predis\Client;


class Logger {

    private $log;

    public function __construct() {
        $this->log = new \Monolog\Logger('eduConnector');
        $this->log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());

        /*
        * Log to local file
        * */
        $this->log->pushHandler(new \Monolog\Handler\RotatingFileHandler(DATA . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'error.log', 0, \Monolog\Logger::ERROR));
        $this->log->pushHandler(new \Monolog\Handler\RotatingFileHandler(DATA . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'info.log', 0, \Monolog\Logger::INFO));

        /*
        * Log to redis/logstash
        */
        if(defined('REDISSERVER') && !empty(REDISSERVER)) {
            $redisHandler = new RedisHandler(new Client(REDISSERVER), 'phplogs');
            $formatter = new LogstashFormatter('eduConnector');
            $redisHandler->setFormatter($formatter);
            $this->log->pushHandler($redisHandler);
        }
    }

    public function getLog() {
        return $this->log;
    }
}
