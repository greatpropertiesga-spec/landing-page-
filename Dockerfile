FROM php:8.2-apache

# Install system deps + PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql

# cURL is built into PHP — just make sure the system lib is there
RUN apt-get install -y curl

RUN a2enmod rewrite

# PHP config
RUN mkdir -p /tmp/php_sessions && chmod 777 /tmp/php_sessions
RUN echo "session.save_path = /tmp/php_sessions" >> /usr/local/etc/php/php.ini \
 && echo "display_errors = On"  >> /usr/local/etc/php/php.ini \
 && echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini

COPY . /var/www/html/

# Rename numbered files to clean web names
RUN cp /var/www/html/02_config.php    /var/www/html/config.php
RUN cp /var/www/html/03_save_lead.php /var/www/html/save_lead.php
RUN cp /var/www/html/04_login.php     /var/www/html/login.php
RUN cp /var/www/html/05_admin.php     /var/www/html/admin.php
RUN cp /var/www/html/06_logout.php    /var/www/html/logout.php
RUN cp /var/www/html/07_index.php     /var/www/html/index.php

RUN chown -R www-data:www-data /var/www/html /tmp/php_sessions \
 && chmod -R 755 /var/www/html

EXPOSE 80
