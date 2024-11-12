<?php
require_once __DIR__ . '/../../config.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$id = preg_replace('/[^a-f0-9]/', '', $_GET["id"]);

$icon = 'icon.php';
echo json_encode([
    "name" => defined("ONLYOFFICE_EDUSHARING_PLUGIN_LABEL") && ONLYOFFICE_EDUSHARING_PLUGIN_LABEL ? ONLYOFFICE_EDUSHARING_PLUGIN_LABEL : "edu-sharing",
    "guid" => "asc.{b3a20a66-c974-4aa0-8bce-c691d00d558c}",
    "variations" => [
        [
            "description" => defined("ONLYOFFICE_EDUSHARING_PLUGIN_LABEL") && ONLYOFFICE_EDUSHARING_PLUGIN_LABEL ? ONLYOFFICE_EDUSHARING_PLUGIN_LABEL : "edu-sharing",
            "url" => "index.php?id=" . $id . "&",
            "icons" => [$icon, $icon, $icon, $icon],
            "isViewer" => true,
            "isDisplayedInViewer" => false,
            "EditorsSupport" => ["word", "cell", "slide"],
            "isVisual" => true,
            "isModal" => true,
            "isInsideMode" => false,
            "initDataType" => "ole",
            "initData" => "",
            "isUpdateOleOnResize" => false,
            "buttons" => [
                [
                    "text" => "Ok",
                    "primary" => true
                ],
                [
                    "text" => "Cancel",
                    "primary" => false,
                    "isViewer" => false,
                    "textLocale" => [
                        "ru" => "Отмена",
                        "fr" => "Annuler",
                        "es" => "Cancelar",
                        "de" => "Abbrechen"
                    ]
                ]
            ],
            "size" => [350, 105]
        ]
    ]
]);