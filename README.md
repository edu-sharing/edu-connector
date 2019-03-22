# Installation
Ensure that the connector can do cURL requests  to the desired edu-sharing repository REST API as well as SOAP calls.

## Basic installation
1. Rename `config.dist.php` to `config.php`
2. In `config.php` set values for
    - __WWWURL__ URL ot the connector, e.g. `https://your.server/eduConnector`
    - __DOCROOT__ path on your server, e.g. `/var/www/html/eduConnector`
3. Install dependencies with composer
4. Register connector in your edu-sharing repository with URL `https://your.server/eduConnector/metadata`

## Tool installation
### TinyMCE
The TinyMCE WYSIWYG editor runs out of the box. What a luck!
### H5P
To use the H5P editor you need to install and configure a database. At this time only __MySQL/MariaDB__ is supported.
- Create database and database user and grant all priviliges to him.
- Set values in config.php
    - __DBHOST__ database host, e.g. `localhost`
    - __DBUSER__ database user
    - __DBPASSWORD__ the users password
    - __DBNAME__ name of the database
- Create database tables by executing `php eduConnector/src/tools/h5p/createDb.php`
### OnlyOffice
- Setup the OnlyOffice Document Server (see https://helpcenter.onlyoffice.com/de/server/document.aspx)
- Set values for
    - __ONLYOFFICE_DOCUMENT_SERVER__ URL of the document server
    - __ONLYOFFICE_PLUGIN_URL__ optional, if you want to use custom plugins
### ONYX
- Setup the ONYX editor
- Set values for
    - __ONYXURL__ URL to the ONYX editor
    - __ONYXPUB__ SSL public key of the ONYX editor
    - __REPOSITORY__ internal id of the repository generated by ONYX editor
### etherpad
- Setup etherpad or etherpad lite
- Set values for
    - __ETHERPAD_SERVER__ URL to etherpad (maybe with port) without scheme, e.g. `your.etherpad:1234`
    - __ETHERPAD_PROTOCOL__ scheme of the etherpad, e.g. `https`
    - __ETHERPAD_APIKEY__ API key generated by etherpad

#Troubleshooting
The error logs can be found in teh `log` folder.

#Hints & ToDos
## etherpad
New pads will be created but the reference is not saved to repository at this time. See `Etherpad.php` for details.
##H5P
As all required metadata is handled in edu-sharing itself, it will be ignored in editor. uploading of content is disabled as it should be done in edu-sharing workspace.
