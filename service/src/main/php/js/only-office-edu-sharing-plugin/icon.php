<?php

require_once __DIR__ . '/../../config.php';

if(defined("ONLYOFFICE_EDUSHARING_PLUGIN_ICON") && ONLYOFFICE_EDUSHARING_PLUGIN_ICON) {
    header('Content-Type: image/png');
    echo file_get_contents(ONLYOFFICE_EDUSHARING_PLUGIN_ICON);
} else {
    header('Content-Type: image/svg+xml');
    echo file_get_contents('icon.svg');
}