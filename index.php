<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap.php';
$connector = new \connector\lib\Connector($log);
