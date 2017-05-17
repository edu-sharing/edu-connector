<?php

$log = new \Monolog\Logger();
/*
 * @see https://odolbeau.fr/blog/when-monolog-meet-elk.html
 */
$log->pushHandler(new Monolog\Handler\RotatingFileHandler(__DIR__ . '/log/connector.log', 0, \Monolog\Logger::DEBUG));
$log->pushProcessor(new Monolog\Processor\IntrospectionProcessor());