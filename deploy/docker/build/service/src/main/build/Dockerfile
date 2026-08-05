FROM ${docker.from.apache.server}

########################################################################################################################

RUN set -eux \
	&& apt-get update -y \
	&& apt-get install -y --no-install-recommends \
        curl \
        jq \
        libpq-dev \
        libzip-dev \
        nano \
        postgresql-client \
        redis-tools \
        wait-for-it \
        xmlstarlet \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip \
    && a2enmod rewrite \
    && ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini \
    && sed -i 's/max_execution_time = 30/max_execution_time = 120/g' $PHP_INI_DIR/php.ini \
    && sed -i 's/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 21600/g' $PHP_INI_DIR/php.ini \
    && sed -i 's/session.use_only_cookies = 1/session.use_only_cookies = 0/g' $PHP_INI_DIR/php.ini \
    && sed -i 's/session.use_strict_mode = 0/session.use_strict_mode = 1/g' $PHP_INI_DIR/php.ini \
    && apt-get clean \
    && rm -r /var/lib/apt/lists/* \
    && curl -L -o /tmp/phpredis.tar.gz https://github.com/phpredis/phpredis/archive/5.3.7.tar.gz \
    && mkdir -p /usr/src/php/ext/redis \
    && tar -C /usr/src/php/ext/redis -xzf /tmp/phpredis.tar.gz --strip 1 \
    && rm -r /tmp/phpredis.tar.gz \
    && docker-php-ext-install redis

########################################################################################################################

ENV PATH /application/bin:$PATH
ENV ROOT /var/www/html
ENV DATA /var/data

WORKDIR $ROOT

########################################################################################################################

COPY artifacts/edu_sharing-community-services-connector-service-${org.edu_sharing:edu_sharing-community-services-connector-service:tar.gz:bin.version}-bin.tar.gz /tmp/edu_sharing-community-services-connector.tar.gz

RUN set -eux \
	&& tar -xzf /tmp/edu_sharing-community-services-connector.tar.gz \
	&& rm -rf /tmp/edu_sharing-community-services-connector.tar.gz

COPY assets/apache2/ports.conf /etc/apache2/ports.conf
COPY assets/apache2/sites/* /etc/apache2/sites-available/

RUN set -eux \
	&& a2dissite 000-default \
	&& a2ensite external \
	&& a2ensite internal \
    && a2ensite metrics

########################################################################################################################

COPY assets/entrypoint.sh /usr/local/bin/
COPY assets/init.sh /usr/local/bin/

RUN set -eux \
	&& find /usr/local/bin -type f -name '*.sh' -exec chmod +x {} \; \
    && addgroup --gid 1000 appuser \
    && adduser --uid 1000 --gid 1000 --disabled-password appuser \
    && adduser www-data appuser \
    && mkdir -p $DATA \
	&& chown -R appuser:appuser /etc/apache2 $ROOT $DATA $PHP_INI_DIR

USER appuser

########################################################################################################################

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]

VOLUME [ "/var/data" ]

########################################################################################################################

LABEL git.branch=${git.branch} \
git.closest.tag.name=${git.closest.tag.fixed} \
git.commit.id=${git.commit.id} \
git.dirty=${git.dirty} \
mvn.project.artifactId=${project.artifactId} \
mvn.project.groupId=${project.groupId} \
mvn.project.version=${project.version}
