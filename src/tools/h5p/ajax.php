<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';

global $db;
$db = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASSWORD);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

header('Content-Type: application/json');


$H5PFramework = new H5PFramework();
$H5PCore = new H5PCore($H5PFramework, $H5PFramework->get_h5p_path(), $H5PFramework->get_h5p_url(), LANG, false);
$H5PEditor = new H5peditor( $H5PCore, new H5peditorStorageImpl(), new H5PEditorAjaxImpl());

switch($_GET['action']) {

    case 'h5p_libraries':

        if(isset($_GET['machineName']) && isset($_GET['majorVersion']) && isset($_GET['minorVersion'])) {
            $H5PEditor->ajax->action(H5PEditorEndpoints::SINGLE_LIBRARY, $_GET['machineName'],
                $_GET['majorVersion'], $_GET['minorVersion'], LANG, '',
                $H5PFramework->get_h5p_path()
            );
        } else {
            $H5PEditor->ajax->action(H5PEditorEndpoints::LIBRARIES);
        }

        break;

    case 'h5p_files':
        $token = '';//$_GET['token'];
        $contentId = NULL; // always content crateion - $_POST ['contentId']
        $H5PEditor->ajax->action(H5PEditorEndpoints::FILES, $token, $contentId);
        break;


    default:
        print('INVALID ACTION');

}

exit();