FROM php:8.1-apache

RUN  set -eux \
         && apt-get update \
         && apt-get install -yqq git wait-for-it  \
         && a2enmod rewrite \
         && ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini \
         && docker-php-ext-install pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- \
--install-dir=/usr/bin --filename=composer

WORKDIR /var/www/html
COPY css/ css/
COPY fonts/ fonts/
COPY img/ img/
COPY install/ install/
COPY js/ js/
COPY lang/ lang/
COPY src/ src/
COPY templates/ templates/
COPY composer.json .
COPY composer.lock .
COPY bootstrap.php .
COPY config.dist.php .
COPY defines.php .
COPY index.php .
COPY version.php .

# COPY ../config.dist.php config.php
RUN mkdir /log && chmod -R 777 /log

RUN composer install

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["bash", "/entrypoint.sh"]
