<?php
require __DIR__ . '/../../../vendor/autoload.php';

session_start();

error_reporting(0);
ini_set('display_errors', 0);

$id = $_GET['id'];
$lang = 'de';

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
 * Pursuant to Section 7 § 3(b) of the GNU GPL you must retain the original ONLYOFFICE logo which contains 
 * relevant author attributions when distributing the software. If the display of the logo in its graphic 
 * form is not reasonably feasible for technical reasons, you must include the words "Powered by ONLYOFFICE" 
 * in every copy of the program you distribute. 
 * Pursuant to Section 7 § 3(e) we decline to grant you any rights under trademark law for use of our trademarks.
 *
*/

require_once(__DIR__ . '/config.php');
require_once __DIR__ . '/../../../defines.php';
require_once(__DIR__ . '/common.php');
require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/jwt_helper.php');

if(false === filter_var($id, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-z0-9]*$/"))) || empty($_GET['id'])) {
    header('Location: ' . '../../../error/' . ERROR_DEFAULT);
    exit;
}

if (empty($_SESSION[$id])) {
    $permalink = base64_decode($_GET['ref']);
    $permalinkwithoutversion = substr($permalink, 0, strripos($permalink, '/'));
    header('Location: ' . $permalinkwithoutversion . '?editor=ONLY_OFFICE');
    exit;
}

$filename = $_SESSION[$id]["fileUrl"];
$fileuri = FileUri($filename);

//setcookie('EDUCONNECTOR', getDocEditorKey(), 0, '/', '.metaventis.com');

function getDocEditorKey($id) {
    $node = $_SESSION[$id]['node']->node;
    // use the unique id (which is the original id in case of a collection) to make sure everyone edits the real content
    $nodeId = $node->originalId ? $node->originalId : $node->ref->id;
    if (!empty($node->contentVersion)){
        $contentVersion = $node->contentVersion;
    }else{
        // since  repo 6.0
        $contentVersion = $node->content->version;
    }
    //$revisionId = GenerateRevisionId(md5($nodeId));
    $revisionId = GenerateRevisionId(md5($contentVersion . $nodeId));
    return $revisionId;
}

function getCallbackUrl($id) {
    return rtrim(WEB_ROOT_URL, '/') . '/'
        . "webeditor-ajax.php?type=track"
        . "&key=" . getDocEditorKey($id);
}

//additional entry for callback
$_SESSION['id_'.getDocEditorKey($id)] = $id;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="<?php echo $_SESSION[$id]['WWWURL']?>/css/materialize.min.css">
    <title>edu-sharing ONLYOFFICE</title>

    <style>
        html {
            height: 100%;
            width: 100%;
        }

        body {
            background: #fff;
            color: #333;
            font-family: Arial, Tahoma, sans-serif;
            font-size: 12px;
            font-weight: normal;
            height: 100%;
            margin: 0;
            overflow-y: hidden;
            padding: 0;
            text-decoration: none;
        }

        form {
            height: 100%;
        }

        div {
            margin: 0;
            padding: 0;
        }
    </style>

    <script type="text/javascript" src="<?php echo $GLOBALS["DOC_SERV_API_URL"] ?>"></script>
    <script src="<?php echo $_SESSION[$id]['WWWURL']?>/lang/<?php echo $lang ?>.js"></script>
    <script src="<?php echo $_SESSION[$id]['WWWURL']?>/js/jquery-3.2.1.min.js"></script>
    <script src="<?php echo $_SESSION[$id]['WWWURL']?>/js/materialize.min.js"></script>
    <script type="text/javascript">

        var docEditor;

        var innerAlert = function (message) {
            if (console && console.log)
                console.log(message);
        };

        var onReady = function () {
            innerAlert("Document editor ready");
        };

        var onDocumentStateChange = function (event) {
            var title = document.title.replace(/\*$/g, "");
            document.title = title + (event.data ? "*" : "");
        };

        var onRequestEditRights = function () {
            return;
        };

        var onError = function (event) {
            if (event)
                innerAlert(event.data);
        };
        var config = JSON.parse(
            <?php
            $payload_title = empty($_SESSION[$id]['node']->node->title) ? $_SESSION[$id]['node']->node->name : $_SESSION[$id]['node']->node->title;
            $payload_fileType = $_SESSION[$id]['filetype'];
            $payload_key = getDocEditorKey($id);
            $payload_created = date_format(date_create($_SESSION[$id]['node']->node->createdAt), 'd.m.Y');
            $payload_author = $_SESSION[$id]['node']->node->createdBy->firstName . ' ' . $_SESSION[$id]['node']->node->createdBy->lastName;
            $payload_download = false;
            //$payload_print = $get_array["embed"] == "true" ? "false" : "true";
            $payload_edit = $_SESSION[$id]['edit'] ? true : false;
            //$payload_comment = $get_array["comment"] == "true" ? "true" : "false";
            //$payload_review = $get_array["review"] == "true" ? "true" : "false";
            $payload_form = false;
            $payload_mode = 'edit';
            $detector = new Mobile_Detect();
            $type = $detector->isMobile() ? 'mobile' : 'desktop';
            $payload_callback = getCallbackUrl($id);
            $payload_user = session_id();
            $payload_fname = $_SESSION[$id]['user']->profile->firstName;
            $payload_lname = $_SESSION[$id]['user']->profile->lastName;
            //$payload_save = $get_array["path"];
            $payload = [
                "width" => "100%",
                "height" => "100%",
                "type" => $type, // embedded
                "documentType" => getDocumentType('dummy.' . $_SESSION[$id]['filetype']),
                "document" => [
                    "title" => $payload_title,
                    "url" => $fileuri,
                    "fileType" => $payload_fileType,
                    "key" => $payload_key,
                    "info" => [
                        "author" => $payload_author,
                        "created" => $payload_created,
                    ],
                    "permissions" => [
                        "download" => false,
                        "edit" => $payload_edit
                    ]
                ],
                "editorConfig" => [
                    "mode" => $payload_mode,
                    "lang" => $lang,
                    "callbackUrl" => $payload_callback,
                    "user" => [
                        "id" => $payload_user,
                        "name" => $payload_fname . ' ' . $payload_lname
                        //"firstname" => $payload_fname,
                        //"lastname" => $payload_lname,
                    ],
                    "embedded" => [
                        "saveUrl" => "",
                        "embedUrl" => "",
                        "shareUrl" => "",
                        "toolbarDocked" => "top"
                    ],
                    "customization" => [
                        "about" => false,
                        "feedback" => false,
                        "comments" => true,
                        "forcesave" => false, //check concept, some integrity issues with versions
                        "chat" => true
                        //  goback: {
                        /*   url: "<?php echo serverPath() ?>/index.php",*/
                        // },
                    ]
                ]
            ];
            echo json_encode(json_encode($payload));
            ?>
        );

        config.events = {
            'onReady': onReady,
            'onDocumentStateChange': onDocumentStateChange,
            'onRequestEditRights': onRequestEditRights,
            'onError': onError,
            'onInfo': function ( data ) {
                if ( data && data.data && data.data.getConfig ) {
                    docEditor.serviceCommand ( 'getConfig', '<?php echo $_SESSION[$id]['ticket']; ?>' );
                }
            }
        };
        <?php if (defined('ONLYOFFICE_JWT_SECRET') && !empty(ONLYOFFICE_JWT_SECRET)): ?>
        config.token = "<?php
            $token = ["payload" => $payload];
            echo JWT::encode($token, ONLYOFFICE_JWT_SECRET);
            ?>"
        <?php endif; ?>;
        var сonnectEditor = function () {
            docEditor = new DocsAPI.DocEditor("iframeEditor", config);
        }
        if(window.addEventListener)
        {
            window.addEventListener("load", сonnectEditor);
        }
        else
        if (window.attachEvent) {
            window.attachEvent("load", сonnectEditor);
        }
        function getXmlHttp() {
            var xmlhttp;
            try {
                xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (ex) {
                    xmlhttp = false;
                }
            }
            if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
                xmlhttp = new XMLHttpRequest();
            }
            return xmlhttp;
        }

        /*  function destroy(text) {
              $('#theTextarea').html('');
              $('#modalHeading').html(text[0]);
              $('#modalText').html(text[1]);
              $('#modalButton').html(text[2]);
              $('#modal').modal({
                  dismissible: false,
                  opacity: .8,
              });
              $('#modal').modal('open');
          }

          window.addEventListener("message", function() {
              if(event.data.event=='USER_LOGGED_OUT') {
                  destroy([language.invalidsessionheading, language.invalidsessiontext, language.closeeditor]);
              }
          }, false);
  */

    </script>
</head>
<body>
<form id="form1">
    <div id="iframeEditor">
    </div>
</form>
<div id="modal" class="modal">
    <div class="modal-content">
        <h4 id="modalHeading"></h4>
        <p id="modalText"></p>
        <div style="text-align: right">
            <a id="modalButton" class="waves-effect waves-light btn" onclick="javascript:window.close()"></a>
        </div>
    </div>
</div>
</body>
</html>
