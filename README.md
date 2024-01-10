# Installation
Ensure that the connector can do cURL requests  to the desired edu-sharing repository REST API as well as SOAP calls. Enable module rewrite in the Apache webserver.

## Basic installation
1. Rename `config.dist.php` to `config.php`
2. In `config.php` set values for
    - __WWWURL__ URL ot the connector, e.g. `https://your.server/eduConnector`
    - __DOCROOT__ path on your server, e.g. `/var/www/html/eduConnector`
    - __DATA__ path of the data directory, e.g. `/var/cache/eduConnector`
3. Create __DATA__ directory and set write permission for the web user
4. Install subdirectories to __DATA__ and generate SSL keys by executing `php eduConnector/install/install.php`
5. Install dependencies with composer
6. Register connector in your edu-sharing repository using URL `https://your.server/eduConnector/metadata`

## Tool installation
### TinyMCE
The TinyMCE WYSIWYG editor runs out of the box. What a luck!
### H5P
To use the H5P editor you need to install and configure a database. At this time only __MySQL/MariaDB__ is supported.
- Create database and database user and grant all priviliges to him.
- Set values for
    - __DBHOST__ database host, e.g. `localhost`
    - __DBUSER__ database user
    - __DBPASSWORD__ the users password
    - __DBNAME__ name of the database
- Create database tables by executing `php eduConnector/install/createDb.php`

To enable the h5p editing, please add the following into your `edu-sharing.conf` in the repository (Admin Tools / Global system configuration):
```
connectorList.connectors+={
  id:"H5P", icon:"edit", showNew: true, onlyDesktop: true, hasViewMode: false,
  filetypes:[
    {mimetype: "application/zip",filetype: "h5p", ccressourcetype: "h5p", createable: true,editable: true}
  ]
}
```

### OnlyOffice
- Setup the OnlyOffice Document Server (see https://helpcenter.onlyoffice.com/de/server/document.aspx)
- Set values for
    - __ONLYOFFICE_DOCUMENT_SERVER__ URL of the document server
    - __ONLYOFFICE_PLUGIN_URL__ optional, if you want to use custom plugins
To enable the OnlyOffice editing, please add the following into your `edu-sharing.conf` in the repository (Admin Tools / Global system configuration):
```
connectorList.connectors+={
  id:"ONLY_OFFICE", icon:"edit", showNew: true, onlyDesktop: false, hasViewMode: true,
  filetypes:[
      {mimetype: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",filetype: "docx",createable: true,editable: true},
      {mimetype: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",filetype: "xlsx",createable: true,editable: true},
      {mimetype: "application/vnd.openxmlformats-officedocument.presentationml.presentation",filetype: "pptx",createable: true,editable: true}
  ]
}
```

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

# Components diagram
See https://drive.google.com/open?id=1lbkVAzqRh72zoqR0S_Gffvs6CCwl4Kot

# Troubleshooting
The error logs can be found in the `log` folder in the __DATA__ directory.

If you'll get 404 errors check for active `mod_rewrite` in Apache. You can activate it in the default vhost (`000-default`) like this: 

```
<Directory /var/www/html>
 Options indexes FollowSymLinks MultiViews
 AllowOverride All
 Require all granted
</Directory>
```


# Hints & ToDos
## etherpad
New pads will be created but the reference is not saved to repository at this time. See `Etherpad.php` for details.
## H5P
As all required metadata is handled in edu-sharing itself, it will be ignored in editor. uploading of content is disabled as it should be done in edu-sharing workspace.

# Adding new tools
Individual connectors are located at `lib/tools`.

There the functionality to redirect editing requests from edu-sharing to the individual tools api should be implemented.

Each editing request from edu-sharing will include at least:
- A connector id (i.e. which tool should be used)
- A node id (the object where the data should be fetched and stored to)
  - The content can be retrieved and stored via api endpoints at edu-sharing
- A mimetype (the sub type of the connectors type, i.e. in case of office files to switch between doc or spreadsheet)
- Information about the current user (to handle multi-user sessions or locking on tool side)
- Some additional attributes, check `ConnectorServlet` in the edu-sharing repository for further information

**The API's/Interfaces of a tool you want to interconnect should at least offer:**
- Options to provide and fetch the content data of your object
  - Providing a file format it can store and read again based on a binary - so the data get's pushed back to edu-sharing and stored after the user finished editing
  - Having a back channel which provides the data after all users have finished editing
- Handling multi-users in one sessions
  - Either by allowing multiple users to work collaborative OR
  - Locking the object while it is edited by an other user
    (edu-sharing also provides a rudimentary locking mechanism, but it must be also implemented in the `ConnectorServlet` in this case)
- Providing a webapp/webeditor which can be included either via frame, web component or be accessed by url-navigation (i.e. with special parameters)
  - Certain connectors (like tinyMCE or h5p) may also contain their runtime in this connector app itself.

# Code Quality

## PHP Code Sniffer With Security Audit

Run the following command to perform a check with [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) with [Security Audit](https://github.com/FloeDesignTechnologies/phpcs-security-audit):
```sh
# Show warnings and errors
docker run --rm -it --init -v "$PWD:$PWD" -w "$PWD" tophfr/phpcs-security-audit -p .
# Show only errors
docker run --rm -it --init -v "$PWD:$PWD" -w "$PWD" tophfr/phpcs-security-audit -p -n .
```