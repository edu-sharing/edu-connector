<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use connector\lib\EduRestClient;
use EduSharingApiClient\AppAuthException;

$id = preg_replace('/[^a-f0-9]/', '', $_GET["id"]);
if(!defined("ONLYOFFICE_EDUSHARING_PLUGIN") || ONLYOFFICE_EDUSHARING_PLUGIN !== true) {
    die("ONLYOFFICE_EDUSHARING_PLUGIN is disabled");
}
session_start();
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$client = new EduRestClient($id);
try {
    $ticket = $client->getTicket();
} catch (AppAuthException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve ticket: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    "repoUrl" =>  str_replace("/rest", "", $_SESSION[$id]["api_url"]),
    "appID" => "OnlyOffice",
    "ticket" => $ticket,
    "brandingName" => defined("ONLYOFFICE_EDUSHARING_PLUGIN_LABEL") && ONLYOFFICE_EDUSHARING_PLUGIN_LABEL ? ONLYOFFICE_EDUSHARING_PLUGIN_LABEL : "edu-sharing",
]);
