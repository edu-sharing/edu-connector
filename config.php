<?php

/*general*/
define('WWWURL', 'http://appserver9.metaventis.com/eduConDev');
define('WWWROOT', 'http://138.201.17.74/eduConDev');
define('DOCROOT', '/var/www/eduConDev');


/*onlyoffice*/
define('EDITORURL', WWWURL . '/OnlineEditor/doceditor.php');
define('STORAGEFOLDER', 'storage');
define('STORAGEPATH', DOCROOT . '/OnlineEditor/' . STORAGEFOLDER);
define('STORAGEURL', WWWURL . '/OnlineEditor/' . STORAGEFOLDER);
// more in OnlineEditor/config.php

/*onyx*/
define('ONYXURL', 'https://next.bps-system.de/qualityonyxeditor/directlogin');
define('ONYXPUP', '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAryR+PaTupk4AZnphw2Mb
gBs1vYbZ3185mGzx95G7CpY1ZRiDVi+Cmb40tqLDv273Jx61UR0gse4e4QIJkrDQ
Sisc33Btm0Qzs2Ac1e4fkD9+sDTvg8oCvqjllkemGzlWuDHXQDr8Yr6BXrtaOZCZ
2o6rjEWGOmJsL3SqsdnePIuWT9NJ2BH0rSBC00ovFzUNnTp8VsQ98+C+1UnEE2GM
SEUJVXXCJnbwrar+WBRaNKUJ9qDlX4IMZhZLeztyyeO/V6W5d8ucTaOAFYN5vHBG
p3B9w+7OI/Ybio6XjFoG9ZfZlPOjsoARrkQbDtdG6oF5vdgSKuo2g8Eniu4r9s1D
YQIDAQAB
-----END PUBLIC KEY-----');

/*moodle*/
define('MOODLEURL', 'http://138.201.17.74/aMoodleInstance');
define('MOODLETOKEN', 'b484fddb5d2a6a6ba4b6cb4523fa2293');


/*etherpad*/
define('SERVER', '138.201.17.74:9001');
define('PROTOCOL', 'http');
define('APIKEY', '312c7fff8fa84f7ba5467d3cbc165f0acc46153b1fde7c0123a5520b84d4cd50');