<?php

/*general*/
define('WWWURL', 'http://appserver9.metaventis.com/eduConDev');
define('WWWROOT', 'http://138.201.17.74/eduConDev');
define('DOCROOT', '/var/www/eduConDev');
define('REPOURL', 'http://appserver7.metaventis.com:7128/edu-sharing/');
define('CONTENT_URL', REPOURL . 'content');
define('APP_ID', 'educonnector');


/*onlyoffice*/
define('EDITORURL', WWWURL . '/OnlineEditor/doceditor.php');
define('STORAGEFOLDER', 'storage');
define('STORAGEPATH', DOCROOT . '/OnlineEditor/' . STORAGEFOLDER);
define('STORAGEURL', WWWURL . '/OnlineEditor/' . STORAGEFOLDER);
// more in OnlineEditor/config.php


/*moodle*/
define('MOODLEURL', 'http://138.201.17.74/aMoodleInstance');
define('MOODLETOKEN', 'b484fddb5d2a6a6ba4b6cb4523fa2293');


/*etherpad*/
define('SERVER', '138.201.17.74:9001');
define('PROTOCOL', 'http');
define('APIKEY', '312c7fff8fa84f7ba5467d3cbc165f0acc46153b1fde7c0123a5520b84d4cd50');