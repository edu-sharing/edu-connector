<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use connector\lib\EduRestClient;

$id = preg_replace('/[^a-f0-9]/', '', $_GET["id"]);
$phpsessid = preg_replace('/[^a-zA-Z0-9,-]/', '', $_GET['PHPSESSID'] ?? '');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if (!defined("ONLYOFFICE_EDUSHARING_PLUGIN") || ONLYOFFICE_EDUSHARING_PLUGIN !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'ONLYOFFICE_EDUSHARING_PLUGIN is disabled']);
    exit;
}

// The plugin runs in an iframe on the document server's origin. There the session cookie is
// either not sent at all or holds a stale id of the partitioned cookie jar, and a cookie always
// takes precedence over the id in the query, so the id handed over by doceditor.php has to win.
if ($phpsessid !== '') {
    session_id($phpsessid);
}
session_start();

// Every failure has to be reported as JSON: the plugin parses this response, so a fatal error
// would surface in the browser as an unrelated error about the missing configuration.
try {
    if (empty($_SESSION[$id])) {
        throw new RuntimeException('no session data for connector id ' . $id);
    }
    $client = new EduRestClient($id);
    $ticket = $client->getTicket();

    echo json_encode([
        "repoUrl" =>  str_replace("/rest", "", $_SESSION[$id]["api_url"]),
        "appID" => "OnlyOffice",
        "ticket" => $ticket,
        "brandingName" => defined("ONLYOFFICE_EDUSHARING_PLUGIN_LABEL") && ONLYOFFICE_EDUSHARING_PLUGIN_LABEL ? ONLYOFFICE_EDUSHARING_PLUGIN_LABEL : "edu-sharing",
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve ticket: ' . $e->getMessage()]);
}
