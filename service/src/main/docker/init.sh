#!/bin/bash
[[ -n $DEBUG ]] && set -x
set -eu

# REQUIRED
my_host_internal="${CONNECTOR_INTERNAL_HOST:-services-connector}"
my_port_internal="${CONNECTOR_INTERNAL_PORT:-80}"
my_base_internal="http://${my_host_internal}:${my_port_internal}"
my_meta_internal="${my_base_internal}/metadata"

repository_service_prot="${REPOSITORY_SERVICE_PROT:-http}"
repository_service_host="${REPOSITORY_SERVICE_HOST:-repository-service}"
repository_service_port="${REPOSITORY_SERVICE_PORT:-8080}"
repository_service_path="${REPOSITORY_SERVICE_PATH:-}"

repository_service_base="${repository_service_prot}://${repository_service_host}:${repository_service_port}${repository_service_path}"

repository_service_admin_user="admin"
repository_service_admin_pass="${REPOSITORY_SERVICE_ADMIN_PASS:-admin}"

### Wait ###############################################################################################################

until wait-for-it "${my_host_internal}:${my_port_internal}" -t 3; do sleep 1; done

until [[ $( curl -sSf -w "%{http_code}\n" -o /dev/null "${my_meta_internal}" ) -eq 200 ]]
do
	echo >&2 "Waiting for ${my_host_internal} ..."
	sleep 3
done

until wait-for-it "${repository_service_host}:${repository_service_port}" -t 3; do sleep 1; done

until [[ $(curl -sSf -w "%{http_code}\n" -o /dev/null -H 'Accept: application/json' "${repository_service_base}/rest/_about/status/SERVICE?timeoutSeconds=3") -eq 200 ]]; do
	echo >&2 "Waiting for ${repository_service_host} service ..."
	sleep 3
done

########################################################################################################################


my_appid=$( \
	curl -sS "${my_meta_internal}" | xmlstarlet sel -t -v '/properties/entry[@key="appid"]' - | xargs echo \
)

has_my_appid=$( \
	curl -sS \
		-H "Accept: application/json" \
		--user "${repository_service_admin_user}:${repository_service_admin_pass}" \
		"${repository_service_base}/rest/admin/v1/applications" | jq -r '.[] | select(.id == "'"${my_appid}"'") | .id' \
)

if [ -n "${has_my_appid}" ]
then
	curl -sS \
		-H "Accept: application/json" \
		--user "${repository_service_admin_user}:${repository_service_admin_pass}" \
		-XDELETE \
		"${repository_service_base}/rest/admin/v1/applications/${my_appid}"
fi

curl -sS \
  -H "Accept: application/json" \
  --user "${repository_service_admin_user}:${repository_service_admin_pass}" \
  -XPUT \
  "${repository_service_base}/rest/admin/v1/applications?url=$( jq -nr --arg v "${my_meta_internal}" '$v|@uri' )"
