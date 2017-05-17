<?php
session_start();

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap.php';

if($_GET['metadata'] === 'repository') {
    $metadataGenerator = new \connector\lib\MetadataGenerator();
    $metadataGenerator -> serve();
    exit(0);
}

$connector = new \connector\lib\Connector($log);
