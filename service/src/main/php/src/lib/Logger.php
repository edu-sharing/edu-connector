<?php

namespace connector\lib;

use Exception;
use Monolog\Handler\RedisHandler;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Monolog\Processor\IntrospectionProcessor;
use Predis\Client;


class Logger {

    private MonoLogger $log;

    public function __construct() {
        $this->log = new MonoLogger('eduConnector');
        $this->log->pushProcessor(new IntrospectionProcessor());

        /*
        * Log to local file
        * */
        if(LOG_MODE === 'file') {
            $this->log->pushHandler(new RotatingFileHandler(DATA . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'error.log', 0, MonoLogger::ERROR));
            $this->log->pushHandler(new RotatingFileHandler(DATA . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'info.log', 0, MonoLogger::INFO));
        } else if(LOG_MODE === 'stdout') {
            $this->log->pushHandler(new StreamHandler('php://stdout', MonoLogger::INFO));
            $this->log->pushHandler(new StreamHandler('php://stderr', MonoLogger::ERROR));
        } else {
            throw new Exception("invalid LOG_MODE: " . LOG_MODE);
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
