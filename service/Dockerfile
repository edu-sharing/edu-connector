FROM dockerio.mirror.docker.edu-sharing.com/php:8.1.12-apache as builder

RUN  set -eux \
         && apt-get update \
         && apt-get install -yqq git libzip-dev zip \
         && apt-get clean \
         && rm -r /var/lib/apt/lists/* \
         && curl -sS https://getcomposer.org/installer | php -- \
         --install-dir=/usr/bin --filename=composer

WORKDIR /var/www/html
COPY src/main/php .

RUN composer install && rm -f composerinstaller.php composer.phar

########################################################################################################################

FROM dockerio.mirror.docker.edu-sharing.com/php:8.1.12-apache

RUN  set -eux \
         && apt-get update \
         && apt-get install -yqq wait-for-it libpq-dev libzip-dev curl xmlstarlet jq nano \
         && a2enmod rewrite \
         && ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini \
         && sed -i 's/max_execution_time = 30/max_execution_time = 120/g' $PHP_INI_DIR/php.ini \
         && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
         && docker-php-ext-install pdo pdo_pgsql pgsql zip \
         && apt-get clean \
         && rm -r /var/lib/apt/lists/*


ARG git_branch=dev
ARG git_closest_tag_fixed=dev
ARG git_commit_id=dev
ARG git_dirty=dev
ARG project_artifactId=edu_sharing-community-services-connector
ARG project_groupId=org.edu_sharing
ARG project_version=dev

ENV PATH /application/bin:$PATH
ENV ROOT /var/www/html
ENV DATA /var/data

WORKDIR $ROOT

COPY --from=builder /var/www/html ./

RUN set -eux \
    && sed -i "s|#BUILD_COMMIT|$git_commit_id|g" install/version_info.php \
    && sed -i "s|#BUILD_BRANCH|$git_branch|g" install/version_info.php \
    && mkdir $DATA \
    && chown -R www-data:www-data $DATA

########################################################################################################################

COPY src/main/docker/entrypoint.sh src/main/docker/init.sh /usr/local/bin/

RUN set -eux \
	&& find /usr/local/bin -type f -name '*.sh' -exec chmod +x {} \; \
    && addgroup --gid 1000 appuser \
    && adduser --uid 1000 --gid 1000 --disabled-password appuser \
    && adduser www-data appuser \
	&& chown -R appuser:appuser /etc/apache2 $ROOT $DATA \
    && chown -R appuser:appuser $PHP_INI_DIR

USER appuser

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]

########################################################################################################################

# TODO default value substituion doesn't work because of the '.' delimiter
LABEL git.branch=${git_branch} \
git.closest.tag.name=${git_closest_tag_fixed} \
git.commit.id=${git_commit_id} \
git.dirty=${git_dirty} \
mvn.project.artifactId=${project_artifactId} \
mvn.project.groupId=${project_groupId} \
mvn.project.version=${project_version}
