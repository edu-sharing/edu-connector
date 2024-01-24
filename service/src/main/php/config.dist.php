<?php

/*general (without trailing slashes)*/
define('VERSION', '');
define('WWWURL', '');
define('DOCROOT', '');
define('DATA', '');

// supported values: file, stdout
define('LOG_MODE', 'file');

/*h5p*/
// either pgsql or mysql
define('DBTYPE', 'pgsql');
define('DBHOST', '');
define('DBPORT', '5432');
define('DBUSER', '');
define('DBPASSWORD', '');
define('DBNAME', '');

/*onlyoffice*/
define('ONLYOFFICE_DOCUMENT_SERVER', '');
define('ONLYOFFICE_PLUGIN_URL', '');
define('ONLYOFFICE_JWT_SECRET', '');
define('ONLYOFFICE_STORAGEFOLDER','storage');

/*onyx*/
define('ONYXURL', '');
define('ONYXPUB', '');
define('REPOSITORY', '');

/*etherpad*/
define('ETHERPAD_SERVER', '');
define('ETHERPAD_PROTOCOL', '');
define('ETHERPAD_APIKEY', '');

/*h5p cache*/
define('H5P_SUPPRESS_CLEANUP', false);