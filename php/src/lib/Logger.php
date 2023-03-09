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
        if(LOG_MODE === 'file') {
            $this->log->pushHandler(new \Monolog\Handler\RotatingFileHandler(DATA . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'error.log', 0, \Monolog\Logger::ERROR));
            $this->log->pushHandler(new \Monolog\Handler\RotatingFileHandler(DATA . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'info.log', 0, \Monolog\Logger::INFO));
        } else if(LOG_MODE === 'stdout') {
            $this->log->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
            $this->log->pushHandler(new \Monolog\Handler\StreamHandler('php://stderr', \Monolog\Logger::ERROR));
        } else {
            throw new \Exception("invalid LOG_MODE: " . LOG_MODE);
        }
        $this->log->info("Logger started in mode " . LOG_MODE);
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
