<?php
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

    require_once( dirname(__FILE__) . '/config.php' );
    require_once( dirname(__FILE__) . '/common.php' );
    require_once( dirname(__FILE__) . '/functions.php' );

    $filename;
    $fileuri;
    $filename = $_SESSION["fileUrl"];
    $fileuri = FileUri($filename);

    setcookie('EDUCONNECTOR', getDocEditorKey(md5($filename . md5_file($fileuri))), 0, '/', '.metaventis.com');

    function getDocEditorKey($fileUri) {
        return GenerateRevisionId(basename($fileUri));
    }


    function getCallbackUrl($fileName) {
        return rtrim(WEB_ROOT_URL, '/') . '/'
                    . "webeditor-ajax.php"
                    . "?type=track&userAddress=" . getClientIp()
                    . "&fileName=" . urlencode($fileName)
                    . "&sess=" . session_id();
    }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="icon" href="./favicon.ico" type="image/x-icon" />
    <title>ONLYOFFICE™</title>

    <style>
        html {
            height: 100%;
            width: 100%;
        }

        body {
            background: #fff;
            color: #333;
            font-family: Arial, Tahoma,sans-serif;
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
        var fileType = "<?php echo strtolower(pathinfo($filename, PATHINFO_EXTENSION)) ?>";

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
            location.href = location.href.replace(RegExp("action=view\&?", "i"), "");
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
                        title: "<?php echo $_SESSION['node']->node->title ?>",
                        url: "<?php echo $fileuri ?>",
                        fileType: fileType,
                        key: "<?php echo getDocEditorKey(md5($filename . md5_file($fileuri))) ?>",

                        info: {
                            author: "<?php echo $_SESSION['node']->node->createdBy->firstName . ' ' . $_SESSION['node']->node->createdBy->lastName ?>",
                            created: "<?php echo date_format(date_create($_SESSION['node']->node->createdAt), 'd.m.Y'); ?>",
                        },

                        permissions: {
                            edit: true,
                            download: false,
                        }
                    },
                    editorConfig: {
                        mode: 'edit',
                        lang: "de",
                        callbackUrl: "<?php echo getCallbackUrl($filename) ?>",

                        user: {
                            id: "",
                            firstname: "<?php echo $_SESSION['person']->profile->firstName ?>",
                            lastname: "<?php echo $_SESSION['person']->profile->lastName ?>",
                        },

                        embedded: {
                            saveUrl: "<?php echo $fileuri ?>",
                            embedUrl: "<?php echo $fileuri ?>",
                            shareUrl: "<?php echo $fileuri ?>",
                            toolbarDocked: "top",
                        },

                        customization: {
                            about: false,
                            feedback: false,
                          //  goback: {
                             //   url: "<?php echo serverPath() ?>/index.php",
                           // },
                        },
                        plugins: {
                            pluginsData: [
                                "edu-sharing/config.json?accessToken=<?php echo $_SESSION['oauth_access_token'] ?>"
                                //"edu-sharing/config.json"
                            ],
                            url: "http://onlyoffice.metaventis.com/docEditorPlugins/"
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

    </script>
</head>
<body>
    <form id="form1">
        <div id="iframeEditor">
        </div>
    </form>
</body>
</html>