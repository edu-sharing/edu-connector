#!/bin/bash
[[ -n $DEBUG ]] && set -x
set -eu

########################################################################################################################

########################################################################################################################

my_bind="${SERVICES_CONNECTOR_SERVICE_BIND:-"0.0.0.0"}"

my_prot_external="${SERVICES_CONNECTOR_SERVICE_PROT_EXTERNAL:-http}"
my_host_external="${SERVICES_CONNECTOR_SERVICE_HOST_EXTERNAL:-connector.services.127.0.0.1.nip.io}"
my_port_external="${SERVICES_CONNECTOR_SERVICE_PORT_EXTERNAL:-9200}"
my_path_external="${SERVICES_CONNECTOR_SERVICE_PATH_EXTERNAL:-}"
my_base_external="${my_prot_external}://${my_host_external}:${my_port_external}${my_path_external}";
my_base_external=${my_base_external//\/&/\\&}

my_host_aliases="${SERVICES_CONNECTOR_SERVICE_HOST_ALIASES:-}"
my_host_aliases=${my_host_aliases//\/&/\\&}

my_host_allow_internal_ip="${SERVICES_CONNECTOR_SERVICE_HOST_ALLOW_INTERNAL_IP:-false}"

my_force_intern_com="${SERVICES_CONNECTOR_REPOSITORY_FORCE_INTERN_COM:-false}"

my_forced_apiurl="${SERVICES_CONNECTOR_REPOSITORY_FORCED_APIURL:-}"
my_forced_apiurl=${my_forced_apiurl//\/&/\\&}

my_prot_internal="${SERVICES_RENDERING_SERVICE_PROT_INTERNAL:-http}"
my_host_internal="${SERVICES_RENDERING_SERVICE_HOST_INTERNAL:-services-connector-service}"
my_port_internal="${SERVICES_RENDERING_SERVICE_PORT_INTERNAL:-8080}"
my_path_internal="${SERVICES_RENDERING_SERVICE_PATH_INTERNAL:-}"
my_base_internal="${my_prot_internal}://${my_host_internal}:${my_port_internal}${my_path_internal}"
my_base_internal=${my_base_internal//\/&/\\&}

my_upload_max_filesize="${SERVICES_CONNECTOR_SERVICE_UPLOAD_FILE_SIZE:-512M}"
my_post_max_size="${SERVICES_CONNECTOR_SERVICE_POST_MAX_SIZE:-513M}"
my_memory_limit="${SERVICES_CONNECTOR_SERVICE_MEMORY_LIMIT:-1024M}"

my_onlyoffice_document_server="${SERVICES_CONNECTOR_ONLYOFFICE_DOCUMENT_SERVER:-}"
my_onlyoffice_plugins="${SERVICES_CONNECTOR_ONLYOFFICE_PLUGINS:-true}"
my_onlyoffice_force_save_enabled="${SERVICES_CONNECTOR_ONLYOFFICE_FORCE_SAVE_ENABLED:-false}"
my_onlyoffice_edusharing_plugin="${SERVICES_CONNECTOR_ONLYOFFICE_EDUSHARING_PLUGIN:-false}"
my_onlyoffice_edusharing_plugin_label="${SERVICES_CONNECTOR_ONLYOFFICE_EDUSHARING_PLUGIN_LABEL:-}"
my_onlyoffice_edusharing_plugin_icon="${SERVICES_CONNECTOR_ONLYOFFICE_EDUSHARING_PLUGIN_ICON:-}"
my_onlyoffice_jwt_secret="${SERVICES_CONNECTOR_ONLYOFFICE_JWT_SECRET:-}"

my_h5p_suppress_cleanup="${SERVICES_CONNECTOR_H5P_SUPPRESS_CLEANUP:-false}"

cache_cluster="${CACHE_CLUSTER:-false}"
cache_database="${CACHE_DATABASE:-0}"
cache_host="${CACHE_HOST:-}"
cache_port="${CACHE_PORT:-}"
cache_prefix="PHPREDIS_CLUSTER_SESSION_CONNECTOR:"

database_host=${SERVICES_CONNECTOR_DATABASE_HOST//\/&/\\&}
database_name=${SERVICES_CONNECTOR_DATABASE_NAME//\/&/\\&}
database_pass=${SERVICES_CONNECTOR_DATABASE_PASS//\/&/\\&}
database_port=${SERVICES_CONNECTOR_DATABASE_PORT//\/&/\\&}
database_user=${SERVICES_CONNECTOR_DATABASE_USER//\/&/\\&}

repository_service_host="${REPOSITORY_SERVICE_HOST:-repository-service}"
repository_service_port="${REPOSITORY_SERVICE_PORT:-8080}"
repository_service_base="http://${repository_service_host}:${repository_service_port}/edu-sharing"

### Wait ###############################################################################################################

[[ -n "${cache_host}" && -n "${cache_port}" ]] && {

	until wait-for-it "${cache_host}:${cache_port}" -t 3; do sleep 1; done

	[[ "${cache_cluster}" == "true" ]] && {
		until [[ $(redis-cli --cluster info "${cache_host}" "${cache_port}" | grep '[OK]' | cut -d ' ' -f5) -gt 1 ]]; do
			echo "."
			sleep 2
		done
	}

}

until wait-for-it "${database_host}:${database_port}" -t 3; do sleep 1; done

until PGPASSWORD="${database_pass}" \
  psql -h "${database_host}" -p "${database_port}" -U "${database_user}" -d "${database_name}" -c '\q'; do
  echo >&2 "Waiting for ${database_host} ..."
  sleep 3
done

until wait-for-it "${repository_service_host}:${repository_service_port}" -t 3; do sleep 1; done

until [[ $(curl -sSf -w "%{http_code}\n" -o /dev/null -H 'Accept: application/json' "${repository_service_base}/rest/_about/status/SERVICE?timeoutSeconds=3") -eq 200 ]]; do
	echo >&2 "Waiting for ${repository_service_host} service ..."
	sleep 3
done

########################################################################################################################

sed -i 's|^Listen \([0-9]+\)|Listen '"${my_bind}"':\1|g' /etc/apache2/ports.conf

sed -i 's|^\(\s*\)[#]*ServerName.*|\1ServerName '"${my_host_external}"'|' /etc/apache2/sites-available/external.conf
sed -i 's|^\(\s*\)[#]*ServerName.*|\1ServerName '"${my_host_internal}"'|' /etc/apache2/sites-available/internal.conf

sed -i 's|^expose_php.*|expose_php = Off|' "${PHP_INI_DIR}/php.ini"

########################################################################################################################

[[ -n "${cache_host}" && -n "${cache_port}" ]] && {

	if [[ ${cache_cluster} == "true" ]] ; then
		sed -i 's|^[;\s]*session\.save_handler.*|session.save_handler = 'rediscluster'|' "${PHP_INI_DIR}/php.ini"
    echo "session.save_path = \"seed[]=${cache_host}:${cache_port}&prefix=${cache_prefix}\"" >> "${PHP_INI_DIR}/php.ini"
	else
		sed -i 's|^[;\s]*session\.save_handler.*|session.save_handler = 'redis'|' "${PHP_INI_DIR}/php.ini"
    echo "session.save_path = \"tcp://${cache_host}:${cache_port}?database=${cache_database}&prefix=${cache_prefix}\"" >> "${PHP_INI_DIR}/php.ini"
	fi

}

########################################################################################################################

php_ini=$PHP_INI_DIR/php.ini

sed -i -r "s|upload_max_filesize.*|upload_max_filesize = ${my_upload_max_filesize}|" "${php_ini}"
sed -i -r "s|post_max_size.*|post_max_size = ${my_post_max_size}|" "${php_ini}"
sed -i -r "s|memory_limit.*|memory_limit = ${my_memory_limit}|" "${php_ini}"

conf="config.php"
cp config.dist.php "${conf}"

sed -i "s|define('WWWURL', '.*')|define('WWWURL', '${my_base_external}')|g" "${conf}"
sed -i "s|define('HOST_ALIASES', '.*')|define('HOST_ALIASES', '${my_host_aliases}')|g" "${conf}"
sed -i "s|define('HOST_ALLOW_INTERNAL_IP', .*)|define('HOST_ALLOW_INTERNAL_IP', ${my_host_allow_internal_ip})|g" "${conf}"
sed -i "s|define('FORCE_INTERN_COM', .*)|define('FORCE_INTERN_COM', ${my_force_intern_com})|g" "${conf}"
sed -i "s|define('FORCED_APIURL', '.*')|define('FORCED_APIURL', '${my_forced_apiurl}')|g" "${conf}"
sed -i "s|define('DOCROOT', '.*')|define('DOCROOT', '${ROOT}')|g" "${conf}"
sed -i "s|define('DATA', '.*')|define('DATA', '${DATA}')|g" "${conf}"
sed -i "s|define('LOG_MODE', '.*')|define('LOG_MODE', 'stdout')|g" "${conf}"

sed -i "s|define('DBTYPE', '.*')|define('DBTYPE', 'pgsql')|g" "${conf}"
sed -i "s|define('DBHOST', '.*')|define('DBHOST', '${database_host}')|g" "${conf}"
sed -i "s|define('DBPORT', '.*')|define('DBPORT', '${database_port}')|g" "${conf}"
sed -i "s|define('DBUSER', '.*')|define('DBUSER', '${database_user}')|g" "${conf}"
sed -i "s|define('DBPASSWORD', '.*')|define('DBPASSWORD', '${database_pass}')|g" "${conf}"
sed -i "s|define('DBNAME', '.*')|define('DBNAME', '${database_name}')|g" "${conf}"

sed -i "s|define('ONLYOFFICE_DOCUMENT_SERVER', '.*')|define('ONLYOFFICE_DOCUMENT_SERVER', '${my_onlyoffice_document_server}')|g" "${conf}"
sed -i "s|define('ONLYOFFICE_PLUGINS', .*)|define('ONLYOFFICE_PLUGINS', ${my_onlyoffice_plugins})|g" "${conf}"
sed -i "s|define('ONLYOFFICE_FORCE_SAVE_ENABLED', .*)|define('ONLYOFFICE_FORCE_SAVE_ENABLED', ${my_onlyoffice_force_save_enabled})|g" "${conf}"
sed -i "s|define('ONLYOFFICE_EDUSHARING_PLUGIN', .*)|define('ONLYOFFICE_EDUSHARING_PLUGIN', ${my_onlyoffice_edusharing_plugin})|g" "${conf}"
sed -i "s|define('ONLYOFFICE_EDUSHARING_PLUGIN_LABEL', '.*')|define('ONLYOFFICE_EDUSHARING_PLUGIN_LABEL', '${my_onlyoffice_edusharing_plugin_label}')|g" "${conf}"
sed -i "s|define('ONLYOFFICE_EDUSHARING_PLUGIN_ICON', '.*')|define('ONLYOFFICE_EDUSHARING_PLUGIN_ICON', '${my_onlyoffice_edusharing_plugin_icon}')|g" "${conf}"
sed -i "s|define('ONLYOFFICE_JWT_SECRET', '.*')|define('ONLYOFFICE_JWT_SECRET', '${my_onlyoffice_jwt_secret}')|g" "${conf}"

sed -i "s|define('H5P_SUPPRESS_CLEANUP', false)|define('H5P_SUPPRESS_CLEANUP', ${my_h5p_suppress_cleanup})|g" "${conf}"

echo "Installing..."
php install/install.php

echo "Initializing database..."
php install/createDb.php

echo ""
echo ""
echo "Connector is ready. Please register it at your repository (Admin Tools -> Remote-Systems) with the following url:"
echo "${my_base_external}/metadata"
echo ""
echo ""

########################################################################################################################

exec "$@"