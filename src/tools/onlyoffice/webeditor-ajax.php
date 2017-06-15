<?php
//session_id($_GET['sess']);
//session_start();

require __DIR__ . '/../../../vendor/autoload.php';
$lang = 'de';
$logger = new connector\lib\Logger();
$log = $logger->getLog();
/*$id = $_GET['id'];

if(empty($_SESSION[$id]) || empty($_GET['id'])) {
    $log -> error('Empty value for id.');
    $log -> error(json_encode($_GET, true));
    exit();
}*/

/*
 *
 * (c) Copyright Ascensio System Limited 2010-2016
 *
 * This program is freeware. You can redistribute it and/or modify it under the terms of the GNU 
 * General Public License (GPL) version 3 as published by the Free Software Foundation (https://www.gnu.org/copyleft/gpl.html). 
 * In accordance with Section 7(a) of the GNU GPL its Section 15 shall be amended to the effect that 
 * Ascensio System SIA expressly excludes the warranty of non-infringement of any third-party rights.
 *
 * THIS PROGRAM IS DISTRIBUTED WITHOUT ANY WARRANTY; WITHOUT EVEN THE IMPLIED WARRANTY OF MERCHANTABILITY OR
 * FITNESS FOR A PARTICULAR PURPOSE. For more details, see GNU GPL at https://www.gnu.org/copyleft/gpl.html
 *
 * You can contact Ascensio System SIA by email at sales@onlyoffice.com
 *
 * The interactive user interfaces in modified source and object code versions of ONLYOFFICE must display 
 * Appropriate Legal Notices, as required under Section 5 of the GNU GPL version 3.
 *
 * Pursuant to Section 7 � 3(b) of the GNU GPL you must retain the original ONLYOFFICE logo which contains 
 * relevant author attributions when distributing the software. If the display of the logo in its graphic 
 * form is not reasonably feasible for technical reasons, you must include the words "Powered by ONLYOFFICE" 
 * in every copy of the program you distribute. 
 * Pursuant to Section 7 � 3(e) we decline to grant you any rights under trademark law for use of our trademarks.
 *
*/

require_once( dirname(__FILE__) . '/config.php' );
require_once( dirname(__FILE__) . '/ajax.php' );
require_once( dirname(__FILE__) . '/common.php' );
require_once( dirname(__FILE__) . '/functions.php' );

$_trackerStatus = array(
    0 => 'NotFound',
    1 => 'Editing',
    2 => 'MustSave',
    3 => 'Corrupted',
    4 => 'Closed',
    6 => 'ForcedSave'
);


if (isset($_GET["type"]) && !empty($_GET["type"])) { //Checks if type value exists
    $response_array;
    @header( 'Content-Type: application/json; charset==utf-8');
    @header( 'X-Robots-Tag: noindex' );
    @header( 'X-Content-Type-Options: nosniff' );

    nocache_headers();

    $log->info(json_encode($_GET));

    $type = $_GET["type"];
    switch($type) { //Switch case for value of type
        case "track":
            $response_array = track($log);
            $response_array['status'] = 'success';
            die (json_encode($response_array));
        default:
            $response_array['status'] = 'error';
            $response_array['error'] = '404 Method not found';
            die(json_encode($response_array));
    }
}

function track($log) {

    global $_trackerStatus;

    $log->info('Track START');
    $data;
    $result["error"] = 0;

    if (($body_stream = file_get_contents('php://input'))===FALSE){
        $log->error('Bad Request');
        $result["error"] = "Bad Request";
        return $result;
    }

    $data = json_decode($body_stream, TRUE); //json_decode - PHP 5 >= 5.2.0

    session_id($data['users'][0]);
    session_start();

    $id = $_SESSION['id_' . $data['key']];

    if ($data === NULL){
        $log->error('Bad Response');
        $result["error"] = "Bad Response";
        return $result;
    }

    $log->info("InputStream data: " . json_encode($data));

    $status = $_trackerStatus[$data["status"]];

    switch ($status){
        case "MustSave":
        case "Corrupted":
        case "ForcedSave":

            $downloadUri = $data["url"];
            $saved = 1;
            $tmpSavePath = STORAGEPATH . '/' . date("Y-m-d_H-i-s") . '_' . $_SESSION[$id]['node']->node->ref->id . '.' . $_SESSION[$id]['filetype'];
            if($status == 'ForcedSave')
                $comment = 'Manually_saved';
            else
                $comment = 'Saved_on_editor_close';

            if (($new_data = file_get_contents($downloadUri)) === FALSE) {
                $saved = 0;
            } else {
                file_put_contents($tmpSavePath, $new_data, LOCK_EX);
                try {
                    $apiClient = new connector\lib\EduRestClient($id);
                    if ($apiClient->createContentNode($_SESSION[$id]['node']->node->ref->id, $tmpSavePath, \connector\tools\onlyoffice\OnlyOffice::getMimetype($_SESSION[$id]['filetype']), $comment)) {
                        unlink($tmpSavePath);
                        $log->info('SAVED - ' . json_encode(array($_SESSION[$id]['node']->node->ref->id, $tmpSavePath)));
                    }
                } catch (Exception $e) {
                    $log->error('ERROR saving file - ' . json_encode($e->__toString()));
                    $log->error('ERROR unsaved file: ' . $tmpSavePath);
                }
            }

            $result["c"] = "saved";
            $result["status"] = $saved;
            break;
    }

    $log->info('Track result: ' . json_encode($result));
    return $result;
}
