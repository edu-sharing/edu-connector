<?php
require_once __DIR__ . '/../../config.php';
$id = preg_replace('/[^a-f0-9]/', '', $_GET["id"]);
if(!defined("ONLYOFFICE_EDUSHARING_PLUGIN") || ONLYOFFICE_EDUSHARING_PLUGIN !== true) {
    die("ONLYOFFICE_EDUSHARING_PLUGIN is disabled");
}
session_start();
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
echo json_encode([
    "repoUrl" =>  str_replace("/rest", "", $_SESSION[$id]["api_url"]),
    "appID" => "OnlyOffice",
    "ticket" => $_SESSION[$id]["ticket"],
    "brandingName" => defined("ONLYOFFICE_EDUSHARING_PLUGIN_LABEL") && ONLYOFFICE_EDUSHARING_PLUGIN_LABEL ? ONLYOFFICE_EDUSHARING_PLUGIN_LABEL : "edu-sharing",
]);
