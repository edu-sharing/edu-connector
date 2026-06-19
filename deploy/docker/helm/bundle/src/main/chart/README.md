## Parameters

### Global parameters

| Name                  | Description     | Value                                      |
| --------------------- | --------------- | ------------------------------------------ |
| `global.patroni.name` | Name of patroni | `edusharing-services-connector-postgresql` |

### Local parameters

| Name                                                                    | Description                                                       | Value                                                             |
| ----------------------------------------------------------------------- | ----------------------------------------------------------------- | ----------------------------------------------------------------- |
| `nameOverride`                                                          | Override name                                                     | `edusharing-services-connector`                                   |
| `edusharing_services_connector_postgresql.enabled`                      | Enable postgresql connector service                               | `true`                                                            |
| `edusharing_services_connector_postgresql.image.name`                   | Set postgresql connector service image name                       | `${docker.edu_sharing.community.common.postgresql.name}`          |
| `edusharing_services_connector_postgresql.image.tag`                    | Set postgresql connector service image tag                        | `${docker.edu_sharing.community.common.postgresql.tag}`           |
| `edusharing_services_connector_postgresql.nameOverride`                 | Override edusharing postgresql connector service name             | `edusharing-services-connector-postgresql`                        |
| `edusharing_services_connector_postgresql.service.port.api`             | Set port for postgresql connector service api                     | `5432`                                                            |
| `edusharing_services_connector_postgresql.config.database`              | Set postgresql connector service database name                    | `connector`                                                       |
| `edusharing_services_connector_postgresql.config.username`              | Set postgresql connector service database username                | `connector`                                                       |
| `edusharing_services_connector_postgresql.init.permission.image.name`   | Set postgresql connector service init permission container name   | `${docker.edu_sharing.community.common.minideb.name}`             |
| `edusharing_services_connector_postgresql.init.permission.image.tag`    | Set postgresql connector service init permission container tag    | `${docker.edu_sharing.community.common.minideb.tag}`              |
| `edusharing_services_connector_postgresql.init.upgrade.image.name`      | Set postgresql repository init upgrade image name                 | `${docker.edu_sharing.community.common.postgresql.upgrade.name}`  |
| `edusharing_services_connector_postgresql.init.upgrade.image.tag`       | Set postgresql repository init upgrade image tag                  | `${docker.edu_sharing.community.common.postgresql.upgrade.tag}`   |
| `edusharing_services_connector_postgresql.job.dump.image.name`          | Set postgresql connector service dump job container name          | `${docker.edu_sharing.community.common.postgresql.name}`          |
| `edusharing_services_connector_postgresql.job.dump.image.tag`           | Set postgresql connector service dump job container tag           | `${docker.edu_sharing.community.common.postgresql.tag}`           |
| `edusharing_services_connector_postgresql.sidecar.metrics.image.name`   | Set postgresql connector service metrics sidecar name             | `${docker.edu_sharing.community.common.postgresql.exporter.name}` |
| `edusharing_services_connector_postgresql.sidecar.metrics.image.tag`    | Set postgresql connector service metrics sidecar tag              | `${docker.edu_sharing.community.common.postgresql.exporter.tag}`  |
| `edusharing_services_connector_rediscluster.enabled`                    | Enable rediscluster connector service                             | `true`                                                            |
| `edusharing_services_connector_rediscluster.image.name`                 | Set rediscluster connector service image name                     | `${docker.edu_sharing.community.common.redis-cluster.name}`       |
| `edusharing_services_connector_rediscluster.image.tag`                  | Set rediscluster connector service image tag                      | `${docker.edu_sharing.community.common.redis-cluster.tag}`        |
| `edusharing_services_connector_rediscluster.nameOverride`               | Override edusharing rediscluster connector service name           | `edusharing-services-connector-rediscluster`                      |
| `edusharing_services_connector_rediscluster.service.port.api`           | Set port for rediscluster connector service api                   | `6379`                                                            |
| `edusharing_services_connector_rediscluster.init.permission.image.name` | Set rediscluster connector service init permission container name | `${docker.edu_sharing.community.common.minideb.name}`             |
| `edusharing_services_connector_rediscluster.init.permission.image.tag`  | Set rediscluster connector service init permission container tag  | `${docker.edu_sharing.community.common.minideb.tag}`              |
| `edusharing_services_connector_rediscluster.init.sysctl.image.name`     | Set rediscluster connector service init sysctl container name     | `${docker.edu_sharing.community.common.minideb.name}`             |
| `edusharing_services_connector_rediscluster.init.sysctl.image.tag`      | Set rediscluster connector service init sysctl container tag      | `${docker.edu_sharing.community.common.minideb.tag}`              |
| `edusharing_services_connector_rediscluster.sidecar.metrics.image.name` | Set rediscluster connector service metrics sidecar name           | `${docker.edu_sharing.community.common.redis.exporter.name}`      |
| `edusharing_services_connector_rediscluster.sidecar.metrics.image.tag`  | Set rediscluster connector service metrics sidecar tag            | `${docker.edu_sharing.community.common.redis.exporter.tag}`       |
| `edusharing_services_connector_service.enabled`                         | Enable connector service                                          | `true`                                                            |
| `edusharing_services_connector_service.config.database.host`            | Set connector service database host                               | `edusharing-services-connector-postgresql`                        |
| `edusharing_services_connector_service.config.database.port`            | Set connector service database port                               | `5432`                                                            |
| `edusharing_services_connector_service.config.database.database`        | Set connector service database name                               | `connector`                                                       |
| `edusharing_services_connector_service.config.database.username`        | Set connector service database username                           | `connector`                                                       |
