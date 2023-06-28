<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = new \connector\lib\Database();
if(DBTYPE === 'mysql') {
    $pdo->exec("SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';");
    $pdo -> query("CREATE DATABASE IF NOT EXISTS `".DBNAME."`");
    $pdo -> query("USE `".DBNAME."`");
}

// refer to https://h5p.org/sites/default/files/class-h5p-plugin.txt


try {
    $result = $pdo->query("SELECT 1 FROM h5p_contents LIMIT 1");
    echo "Tables already exists";
    exit();
} catch (Exception $e) {
}

$h5p_ddl = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '_tmpl' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'h5p.ddl');
$stmt = $pdo->exec($h5p_ddl);

echo 'Success';
exit();
