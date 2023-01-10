#!/bin/bash
set -eu

cd /var/www/html
cp config.dist.php config.php

WWWURL=$(printf '%s\n' "$WWWURL" | sed -e 's/[\/&]/\\&/g')
DBPATH=$(printf '%s\n' "http://$DBHOST:$DBPORT" | sed -e 's/[\/&]/\\&/g')
DBUSER=$(printf '%s\n' "$DBUSER" | sed -e 's/[\/&]/\\&/g')
DBPASSWORD=$(printf '%s\n' "$DBPASSWORD" | sed -e 's/[\/&]/\\&/g')
DBNAME=$(printf '%s\n' "$DBNAME" | sed -e 's/[\/&]/\\&/g')

sed -i "s/define('WWWURL', '')/define('WWWURL', '$WWWURL')/g" config.php
sed -i "s/define('DATA', '')/define('DATA', '\/var\/www\/html\/data')/g" config.php
sed -i "s/define('DBHOST', '')/define('DBHOST', '${DBPATH}')/g" config.php
sed -i "s/define('DBUSER', '')/define('DBUSER', '${DBUSER}')/g" config.php
sed -i "s/define('DBPASSWORD', '')/define('DBPASSWORD', '${DBPASSWORD}')/g" config.php
sed -i "s/define('DBNAME', '')/define('DBNAME', '${DBNAME}')/g" config.php

php install/install.php
until wait-for-it "${DBHOST}:${DBPORT}" -t 3
do
  sleep 5
done
php eduConnector/install/createDb.php

apache2ctl -D FOREGROUND