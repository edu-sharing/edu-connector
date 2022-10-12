FROM php:8.0-apache

RUN  apt-get update -yqq && apt-get install -yqq git && a2enmod rewrite && \
     ln -s $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
RUN curl -sS https://getcomposer.org/installer | php -- \
--install-dir=/usr/bin --filename=composer

WORKDIR /var/www/html
#COPY . .
#COPY config.dist.php config.php
#RUN mkdir /log && chmod -R 777 /log

#RUN composer install