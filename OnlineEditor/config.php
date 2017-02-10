<?php

define('WEB_ROOT_URL', 'http://appserver9.metaventis.com/eduConDev/OnlineEditor');

$GLOBALS['FILE_SIZE_MAX'] = 5242880;
$GLOBALS['STORAGE_PATH'] = "storage";

$GLOBALS['MODE'] = "";

$GLOBALS['DOC_SERV_VIEWD'] = array(".ppt",".pps",".odp",".pdf",".djvu",".epub",".xps");
$GLOBALS['DOC_SERV_EDITED'] = array(".docx",".doc",".odt",".xlsx",".xls",".ods",".csv",".pptx",".ppsx",".rtf",".txt",".mht",".html",".htm");
$GLOBALS['DOC_SERV_CONVERT'] = array(".doc",".odt",".xls",".ods",".ppt",".pps",".odp",".rtf",".mht",".html",".htm",".epub");

$GLOBALS['DOC_SERV_TIMEOUT'] = "120000";



//ein document server
$GLOBALS['DOC_SERV_STORAGE_URL'] = "http://onlyoffice.metaventis.com/FileUploader.ashx";
$GLOBALS['DOC_SERV_CONVERTER_URL'] = "http://onlyoffice.metaventis.com/ConvertService.ashx";
$GLOBALS['DOC_SERV_API_URL'] = "http://onlyoffice.metaventis.com/web-apps/apps/api/documents/api.js";
$GLOBALS['DOC_SERV_PRELOADER_URL'] = "http://onlyoffice.metaventis.com/web-apps/apps/api/documents/cache-scripts.html";



/*
//document server cluster (in entwicklung)
$GLOBALS['DOC_SERV_STORAGE_URL'] = "https://lbonlyoffice.metaventis.com/FileUploader.ashx";
$GLOBALS['DOC_SERV_CONVERTER_URL'] = "https://lbonlyoffice.metaventis.com/FileUploader.ashx";
$GLOBALS['DOC_SERV_API_URL'] = "https://lbonlyoffice.metaventis.com/web-apps/apps/api/documents/api.js";
$GLOBALS['DOC_SERV_PRELOADER_URL'] = "https://lbonlyoffice.metaventis.com/web-apps/apps/api/documents/cache-scripts.html";


$GLOBALS['DOC_SERV_STORAGE_URL'] = "https://appserver7.metaventis.com/FileUploader.ashx";
$GLOBALS['DOC_SERV_CONVERTER_URL'] = "https://appserver7.metaventis.com/FileUploader.ashx";
$GLOBALS['DOC_SERV_API_URL'] = "https://appserver7.metaventis.com/web-apps/apps/api/documents/api.js";
$GLOBALS['DOC_SERV_PRELOADER_URL'] = "https://appserver7.metaventis.com/web-apps/apps/api/documents/cache-scripts.html";
*/


$GLOBALS['ExtsSpreadsheet'] = array(".xls", ".xlsx",
                                    ".ods", ".csv");

$GLOBALS['ExtsPresentation'] = array(".pps", ".ppsx",
                                    ".ppt", ".pptx",
                                    ".odp");

$GLOBALS['ExtsDocument'] = array(".docx", ".doc", ".odt", ".rtf", ".txt",
                                ".html", ".htm", ".mht", ".pdf", ".djvu",
                                ".fb2", ".epub", ".xps");

if ( !defined('ServiceConverterMaxTry') )
    define( 'ServiceConverterMaxTry', 3);


?>