<?php declare(strict_types=1);

use connector\lib\CacheCleaner;

require_once 'src/lib/CacheCleaner.php';

$cacheCleaner = new CacheCleaner();
$cacheCleaner->run();
