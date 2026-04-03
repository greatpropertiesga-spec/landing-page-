FROM php:8.2-apache

# Install PostgreSQL PDO driver for Supabase
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure PHP: sessions + error handling
RUN echo "session.save_path = /tmp" >> /usr/local/etc/php/php.ini \
 && echo "session.gc_probability = 1" >> /usr/local/etc/php/php.ini \
 && echo "display_errors = Off" >> /usr/local/etc/php/php.ini \
 && echo "log_errors = On" >> /usr/local/etc/php/php.ini

# Writable session directory
RUN mkdir -p /tmp/sessions && chmod 777 /tmp/sessions
RUN echo "session.save_path = /tmp/sessions" >> /usr/local/etc/php/php.ini

# Copy all project files
COPY . /var/www/html/

# Rename numbered files to proper web names
RUN cp /var/www/html/02_config.php    /var/www/html/config.php
RUN cp /var/www/html/03_save_lead.php /var/www/html/save_lead.php
RUN cp /var/www/html/04_login.php     /var/www/html/login.php
RUN cp /var/www/html/05_admin.php     /var/www/html/admin.php
RUN cp /var/www/html/06_logout.php    /var/www/html/logout.php
RUN cp /var/www/html/07_index.php     /var/www/html/index.php

# Permissions
RUN chown -R www-data:www-data /var/www/html /tmp/sessions \
    && chmod -R 755 /var/www/html

EXPOSE 80
