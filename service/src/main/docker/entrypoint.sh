#!/bin/bash
[[ -n $DEBUG ]] && set -x
set -eu

cd "$ROOT"

conf="config.php"
cp config.dist.php "${conf}"

# REQUIRED
connector_prot="${PROT_EXTERNAL:-http}"
connector_host="${HOST_EXTERNAL:-localhost}"
connector_port="${PORT_EXTERNAL:-80}"
connector_path="${PATH_EXTERNAL:-}"
connector_url="${connector_prot}://${connector_host}:${connector_port}${connector_path}";
connector_url=${connector_url//\/&/\\&}

connector_database_host=${DATABASE_HOST//\/&/\\&}
connector_database_port=${DATABASE_PORT//\/&/\\&}
connector_database_user=${DATABASE_USER//\/&/\\&}
connector_database_password=${DATABASE_PASSWORD//\/&/\\&}
connector_database_name=${DATABASE_NAME//\/&/\\&}

# OPTIONALS

upload_max_filesize="${UPLOAD_FILE_SIZE:-512M}"
post_max_size="${POST_MAX_SIZE:-513M}"
memory_limit="${MEMORY_LIMIT:-1024M}"

only_office_document_server="${ONLYOFFICE_DOCUMENT_SERVER:-}"
only_office_plugin_url="${ONLYOFFICE_PLUGIN_URL:-}"
only_office_jwt_secret="${ONLYOFFICE_JWT_SECRET:-}"

# shellcheck disable=SC2153
moodle_base_dir="${MOODLE_BASE_DIR:-}"
# shellcheck disable=SC2153
moodle_token="${MOODLE_TOKEN:-}"

php_ini=$PHP_INI_DIR/php.ini

sed -i "s|define('WWWURL', '.*')|define('WWWURL', '${connector_url}')|g" "${conf}"
sed -i "s|define('DOCROOT', '.*')|define('DOCROOT', '${ROOT}')|g" "${conf}"
sed -i "s|define('DATA', '.*')|define('DATA', '${DATA}')|g" "${conf}"
sed -i "s|define('LOG_MODE', '.*')|define('LOG_MODE', 'stdout')|g" "${conf}"
sed -i "s|define('DBTYPE', '.*')|define('DBTYPE', 'pgsql')|g" "${conf}"
sed -i "s|define('DBHOST', '.*')|define('DBHOST', '${connector_database_host}')|g" "${conf}"
sed -i "s|define('DBPORT', '.*')|define('DBPORT', '${connector_database_port}')|g" "${conf}"
sed -i "s|define('DBUSER', '.*')|define('DBUSER', '${connector_database_user}')|g" "${conf}"
sed -i "s|define('DBPASSWORD', '.*')|define('DBPASSWORD', '${connector_database_password}')|g" "${conf}"
sed -i "s|define('DBNAME', '.*')|define('DBNAME', '${connector_database_name}')|g" "${conf}"

sed -i -r "s|upload_max_filesize.*|upload_max_filesize = ${upload_max_filesize}|" "${php_ini}"
sed -i -r "s|post_max_size.*|post_max_size = ${post_max_size}|" "${php_ini}"
sed -i -r "s|memory_limit.*|memory_limit = ${memory_limit}|" "${php_ini}"

sed -i "s|define('ONLYOFFICE_DOCUMENT_SERVER', '.*')|define('ONLYOFFICE_DOCUMENT_SERVER', '${only_office_document_server}')|g" "${conf}"
sed -i "s|define('ONLYOFFICE_PLUGIN_URL', '.*')|define('ONLYOFFICE_PLUGIN_URL', '${only_office_plugin_url}')|g" "${conf}"
sed -i "s|define('ONLYOFFICE_JWT_SECRET', '.*')|define('ONLYOFFICE_JWT_SECRET', '${only_office_jwt_secret}')|g" "${conf}"

sed -i "s|define('MOODLE_BASE_DIR', '.*')|define('MOODLE_BASE_DIR', '${moodle_base_dir}')|g" "${conf}"
sed -i "s|define('MOODLE_TOKEN', '.*')|define('MOODLE_TOKEN', '${moodle_token}')|g" "${conf}"

sed -i "s|define('H5P_SUPPRESS_CLEANUP', false)|define('H5P_SUPPRESS_CLEANUP', ${H5P_SUPPRESS_CLEANUP})|g" "${conf}"

echo "Installing..."
php install/install.php

printf "\nWaiting for postgres to come active\n"
until wait-for-it "${connector_database_host}:${connector_database_port}" -t 3
do
  sleep 5
done

echo "Initializing database..."
php install/createDb.php

echo ""
echo ""
echo "Connector is ready. Please register it at your repository (Admin Tools -> Remote-Systems) with the following url:"
echo "${connector_url}/metadata"
echo ""
echo ""

########################################################################################################################

exec "$@"