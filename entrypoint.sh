#!/bin/bash
set -eu

cd /var/www/html
cp config.dist.php config.php

WWWURL_ESCAPED=$(printf '%s\n' "$WWWURL" | sed -e 's/[\/&]/\\&/g')
DBPATH_ESCAPED=$(printf '%s\n' "$DBHOST:$DBPORT" | sed -e 's/[\/&]/\\&/g')
DBUSER_ESCAPED=$(printf '%s\n' "$DBUSER" | sed -e 's/[\/&]/\\&/g')
DBPASSWORD_ESCAPED=$(printf '%s\n' "$DBPASSWORD" | sed -e 's/[\/&]/\\&/g')
DBNAME_ESCAPED=$(printf '%s\n' "$DBNAME" | sed -e 's/[\/&]/\\&/g')

ONLYOFFICE_DOCUMENT_SERVER_ESCAPED=$(printf '%s\n' "$ONLYOFFICE_DOCUMENT_SERVER" | sed -e 's/[\/&]/\\&/g')
ONLYOFFICE_PLUGIN_URL_ESCAPED=$(printf '%s\n' "$ONLYOFFICE_PLUGIN_URL" | sed -e 's/[\/&]/\\&/g')
ONLYOFFICE_JWT_SECRET_ESCAPED=$(printf '%s\n' "$ONLYOFFICE_JWT_SECRET" | sed -e 's/[\/&]/\\&/g')


sed -i "s/define('WWWURL', '')/define('WWWURL', '${WWWURL_ESCAPED}')/g" config.php
sed -i "s/define('DOCROOT', '')/define('DOCROOT', '\/var\/www\/html')/g" config.php
sed -i "s/define('DATA', '')/define('DATA', '\/var\/www\/html\/data')/g" config.php
sed -i "s/define('LOG_MODE', 'file')/define('LOG_MODE', 'stdout')/g" config.php
sed -i "s/define('DBHOST', '')/define('DBHOST', '${DBPATH_ESCAPED}')/g" config.php
sed -i "s/define('DBUSER', '')/define('DBUSER', '${DBUSER_ESCAPED}')/g" config.php
sed -i "s/define('DBPASSWORD', '')/define('DBPASSWORD', '${DBPASSWORD_ESCAPED}')/g" config.php
sed -i "s/define('DBNAME', '')/define('DBNAME', '${DBNAME_ESCAPED}')/g" config.php

sed -i "s/define('ONLYOFFICE_DOCUMENT_SERVER', '')/define('ONLYOFFICE_DOCUMENT_SERVER', '${ONLYOFFICE_DOCUMENT_SERVER_ESCAPED}')/g" config.php
sed -i "s/define('ONLYOFFICE_PLUGIN_URL', '')/define('ONLYOFFICE_PLUGIN_URL', '${ONLYOFFICE_PLUGIN_URL_ESCAPED}')/g" config.php
sed -i "s/define('ONLYOFFICE_JWT_SECRET', '')/define('ONLYOFFICE_JWT_SECRET', '${ONLYOFFICE_JWT_SECRET_ESCAPED}')/g" config.php
cat config.php

echo "Installing..."
php install/install.php
chown -R www-data:www-data data/
printf "\nWaiting for mysql to come active\n"
until wait-for-it "${DBHOST}:${DBPORT}" -t 3
do
  sleep 5
done

echo "Initializing database..."
php install/createDb.php

printf "\nConnector is ready. Please register it at your repository (Admin Tools -> Remote-Systems) with the following url: \n${WWWURL}/metadata\n\n"

apache2ctl -D FOREGROUND