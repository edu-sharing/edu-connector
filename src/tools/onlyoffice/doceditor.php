<?php
session_start();

$lang = 'de';

if(empty($_SESSION[$id]) || empty($_GET['id']))
    die();

$id = $_GET['id'];
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
require_once(__DIR__ . '/common.php');
require_once(__DIR__ . '/functions.php');

$filename = $_SESSION[$id]["fileUrl"];
$fileuri = FileUri($filename);

//setcookie('EDUCONNECTOR', getDocEditorKey(), 0, '/', '.metaventis.com');

function getDocEditorKey()
{
    return GenerateRevisionId($_SESSION[$id]['node']->node->ref->id . $_SESSION[$id]['node']->node->contentVersion);
}


function getCallbackUrl($filename)
{
    return rtrim(WEB_ROOT_URL, '/') . '/'
        . "webeditor-ajax.php?type=track"
        . "&id=" . $id
        . "&sess=" . session_id();
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>
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

    <script type="text/javascript">

        var docEditor;
        var filetype = "<?php echo $_SESSION[$id]['filetype'] ?>";

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

        var сonnectEditor = function () {

            docEditor = new DocsAPI.DocEditor("iframeEditor",
                {
                    width: "100%",
                    height: "100%",

                    type: "desktop", // embedded
                    documentType: "<?php echo getDocumentType($filename) ?>",
                    document: {
                        title: "<?php echo empty($_SESSION[$id]['node']->node->title)?$_SESSION[$id]['node']->node->name:$_SESSION[$id]['node']->node->title ?>",
                        url: "<?php echo $fileuri ?>",
                        fileType: filetype,
                        key: "<?php echo getDocEditorKey() ?>",

                        info: {
                            author: "<?php echo $_SESSION[$id]['node']->node->createdBy->firstName . ' ' . $_SESSION[$id]['node']->node->createdBy->lastName ?>",
                            created: "<?php echo date_format(date_create($_SESSION[$id]['node']->node->createdAt), 'd.m.Y'); ?>",
                        },

                        permissions: {
                            edit: <?php echo $_SESSION[$id]['edit'] ? 'true' : 'false'; ?>,
                            download: false,
                        }
                    },
                    editorConfig: {
                        mode: 'edit',
                        lang: "de",
                        callbackUrl: "<?php echo getCallbackUrl($filename) ?>",

                        user: {
                            id: "",
                            firstname: "<?php echo $_SESSION[$id]['user']->profile->firstName ?>",
                            lastname: "<?php echo $_SESSION[$id]['user']->profile->lastName ?>",
                        },

                        embedded: {
                            saveUrl: "",
                            embedUrl: "",
                            shareUrl: "",
                            toolbarDocked: "top",
                        },

                        customization: {
                            about: false,
                            feedback: false,
                            //  goback: {
                            //   url: "<?php echo serverPath() ?>/index.php",
                            // },
                        }
                    },
                    events: {
                        'onReady': onReady,
                        'onDocumentStateChange': onDocumentStateChange,
                        'onRequestEditRights': onRequestEditRights,
                        'onError': onError,
                    }
                });
        };

        if (window.addEventListener) {
            window.addEventListener("load", сonnectEditor);
        } else if (window.attachEvent) {
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

        var lastPing = lastEdit = Date.now();

        function destroySession() {
            docEditor.destroy();
            alert('Session abgelaufen. Bitte loggen Sie sich erneut ein um Ihre letzten Änderungen zu speichern.');
        }

        function pingApi() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '<?php echo $_SESSION[$id]['api_url']?>' + 'authentication/v1/validateSession');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    lastPing = Date.now();
                }
                else {
                    destroySession();
                }
            };
            xhr.send();
        }

        setInterval(function(){
            if(Date.now() - lastPing > 500)
                destroySession();
        }, 10000);

        document.getElementById('iframeEditor').addEventListener("keydown", function(e) {
            if(Date.now() - lastEdit > 15  || Date.now() - lastPing > 15) {
                pingApi();
            }
            lastEdit = Date.now();
        }, false);

    </script>
</head>
<body>
<form id="form1">
    <div id="iframeEditor">
    </div>
</form>
</body>
</html>