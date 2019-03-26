<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/lib/Cryptographer.php';


if (!file_exists(DATA)) {
    mkdir(DATA, 0666, true);
}

if (!file_exists(DATA . DIRECTORY_SEPARATOR . 'log')) {
    mkdir(DATA . DIRECTORY_SEPARATOR . 'log', 0666, true);
}

if (!file_exists(DATA . DIRECTORY_SEPARATOR . 'ssl')) {
    mkdir(DATA . DIRECTORY_SEPARATOR . 'ssl', 0666, true);
}

if (!file_exists(DATA . DIRECTORY_SEPARATOR . 'tools')) {
    mkdir(DATA . DIRECTORY_SEPARATOR . 'tools', 0666, true);
}

if (!file_exists(DATA . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'onlyoffice')) {
    mkdir(DATA . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'onlyoffice', 0666, true);
}

if (!file_exists(DATA . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'onlyoffice' . DIRECTORY_SEPARATOR . 'storage')) {
    mkdir(DATA . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'onlyoffice' . DIRECTORY_SEPARATOR . 'storage', 0666, true);
}

$cryptographer = new \connector\lib\Cryptographer();
$cryptographer -> checkPrivateKey();

echo 'Success';
exit();
