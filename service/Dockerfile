FROM php:8.1-apache as builder

RUN  set -eux \
         && apt-get update \
         && apt-get install -yqq git libzip-dev  \
         && apt-get clean \
         && rm -r /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- \
--install-dir=/usr/bin --filename=composer

WORKDIR /var/www/html
COPY src/main/php .

RUN composer install

########################################################################################################################

FROM php:8.1-apache

RUN  set -eux \
         && apt-get update \
         && apt-get install -yqq wait-for-it libzip-dev curl xmlstarlet jq nano \
         && a2enmod rewrite \
         && ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini \
         && docker-php-ext-install pdo pdo_mysql zip \
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
WORKDIR $ROOT

COPY --from=builder /var/www/html/css/ css/
COPY --from=builder /var/www/html/fonts/ fonts/
COPY --from=builder /var/www/html/img/ img/
COPY --from=builder /var/www/html/install/ install/
COPY --from=builder /var/www/html/js/ js/
COPY --from=builder /var/www/html/lang/ lang/
COPY --from=builder /var/www/html/src/ src/
COPY --from=builder /var/www/html/templates/ templates/
COPY --from=builder /var/www/html/vendor/ vendor/
COPY --from=builder /var/www/html/composer.json .
COPY --from=builder /var/www/html/composer.lock .
COPY --from=builder /var/www/html/bootstrap.php .
COPY --from=builder /var/www/html/config.dist.php .
COPY --from=builder /var/www/html/defines.php .
COPY --from=builder /var/www/html/index.php .
COPY --from=builder /var/www/html/version.php .
COPY --from=builder /var/www/html/.htaccess .

RUN set -eux \
    && sed -i "s|#BUILD_COMMIT|$git_commit_id|g" install/version_info.php \
    && sed -i "s|#BUILD_BRANCH|$git_branch|g" install/version_info.php


RUN set -eux \
    && mkdir data \
    && chown -R www-data:www-data data

########################################################################################################################

COPY src/main/docker/entrypoint.sh src/main/docker/init.sh /usr/local/bin/

RUN set -eux \
	&& find /usr/local/bin -type f -name '*.sh' -exec chmod +x {} \;

########################################################################################################################

RUN set -eux \
    && addgroup --gid 1000 appuser \
    && adduser --uid 1000 --gid 1000 --disabled-password appuser \
    && adduser www-data appuser

RUN set -eux \
	&& chown -R appuser:appuser /etc/apache2 $ROOT

USER appuser

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]

########################################################################################################################

# TODO default value substituion doesn't work because of the '.' delimiter
LABEL git.branch=${git_branch}
LABEL git.closest.tag.name=${git_closest_tag_fixed}
LABEL git.commit.id=${git_commit_id}
LABEL git.dirty=${git_dirty}
LABEL mvn.project.artifactId=${project_artifactId}
LABEL mvn.project.groupId=${project_groupId}
LABEL mvn.project.version=${project_version}
