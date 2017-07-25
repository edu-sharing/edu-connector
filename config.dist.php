<?php

/*general*/

//connector url
define('WWWURL', 'http://xxxxxxx/eduConnector');
//connector docroot
define('DOCROOT', '/var/www/html/eduConnector');
//use this to override the api_url parameter retrieved by request
define('FORCED_APIURL', '');
//use intern communication
define('FORCE_INTERN_COM', false);

/*logging*/
//additionally to local log files you can push monolog messages to a redis server
//define('REDISSERVER', 'tcp://xxx.xxx.xxx.xxx:6379');

/*onlyoffice*/
//url the user will be forwarded to
define('ONLYOFFICE_EDITORURL', WWWURL . '/src/tools/onlyoffice/doceditor.php');
//folder for temp documents
define('ONLYOFFICE_STORAGEFOLDER', 'storage');
define('ONLYOFFICE_STORAGEPATH', DOCROOT . '/src/tools/onlyoffice/' . ONLYOFFICE_STORAGEFOLDER);
//webaddress of the document server
define('ONLYOFFICE_DOCUMENT_SERVER', 'http://docserver.edu-sharing.com');
//url of onlyoffice document server plugins
define('ONLYOFFICE_PLUGIN_URL', '');

/*onyx*/
//onyx editor instance the user will be forwarded to
define('ONYXURL', 'http://www.example.com');
//ssl public key of the onyx editor instance
define('ONYXPUB', '-----BEGIN PUBLIC KEY-----
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
-----END PUBLIC KEY-----');
//edu-sharing repository id corresponding to onyx editor instance
define('REPOSITORY', '');

/*etherpad*/
define('ETHERPAD_SERVER', '');
define('ETHERPAD_PROTOCOL', 'http');
define('ETHERPAD_APIKEY', '');
