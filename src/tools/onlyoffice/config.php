<?php

/*
 *  OnlyOffice internal configuration and definitions.
 *  No Changes required here.
 *
 */


require_once __DIR__ . '/../../../config.php';

define('WEB_ROOT_URL', WWWURL . '/onlyoffice');

$GLOBALS['FILE_SIZE_MAX'] = 5242880;
$GLOBALS['STORAGE_PATH'] = STORAGEFOLDER;

$GLOBALS['MODE'] = "";

$GLOBALS['DOC_SERV_VIEWD'] = array(".ppt", ".pps", ".odp", ".pdf", ".djvu", ".epub", ".xps");
$GLOBALS['DOC_SERV_EDITED'] = array(".docx", ".doc", ".odt", ".xlsx", ".xls", ".ods", ".csv", ".pptx", ".ppsx", ".rtf", ".txt", ".mht", ".html", ".htm");
$GLOBALS['DOC_SERV_CONVERT'] = array(".doc", ".odt", ".xls", ".ods", ".ppt", ".pps", ".odp", ".rtf", ".mht", ".html", ".htm", ".epub");

$GLOBALS['DOC_SERV_TIMEOUT'] = "120000";


$GLOBALS['DOC_SERV_STORAGE_URL'] = DOCUMENT_SERVER . "/FileUploader.ashx";
$GLOBALS['DOC_SERV_CONVERTER_URL'] = DOCUMENT_SERVER . "/ConvertService.ashx";
$GLOBALS['DOC_SERV_API_URL'] = DOCUMENT_SERVER . "/web-apps/apps/api/documents/api.js";
$GLOBALS['DOC_SERV_PRELOADER_URL'] = DOCUMENT_SERVER . "/web-apps/apps/api/documents/cache-scripts.html";


$GLOBALS['ExtsSpreadsheet'] = array(".xls", ".xlsx",
    ".ods", ".csv");

$GLOBALS['ExtsPresentation'] = array(".pps", ".ppsx",
    ".ppt", ".pptx",
    ".odp");

$GLOBALS['ExtsDocument'] = array(".docx", ".doc", ".odt", ".rtf", ".txt",
    ".html", ".htm", ".mht", ".pdf", ".djvu",
    ".fb2", ".epub", ".xps");

if (!defined('ServiceConverterMaxTry'))
    define('ServiceConverterMaxTry', 3);


?>