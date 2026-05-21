<?php
require_once __DIR__ . '/../../config.php';
$id = preg_replace('/[^a-f0-9]/', '', $_GET["id"]);
if (!defined("ONLYOFFICE_EDUSHARING_PLUGIN") || ONLYOFFICE_EDUSHARING_PLUGIN !== true) {
    die("ONLYOFFICE_EDUSHARING_PLUGIN is disabled");
}
session_start();

header('Content-Type: text/javascript');
header('Service-Worker-Allowed: /');
header('Cache-Control: no-cache, no-store, must-revalidate');


$apiUrl = str_replace("/rest", "", $_SESSION[$id]["api_url"]);

$curlOptions = [];
$url = $apiUrl . 'web-components/rendering-service-amd/edu-service-worker.js';
$curl = curl_init($url);
curl_setopt_array($curl, $curlOptions);
$content = curl_exec($curl);
$error = curl_errno($curl);
$info = curl_getinfo($curl);
curl_close($curl);
echo $content;
