FROM php:8.2-apache

# Install PostgreSQL PDO driver for Supabase
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy all project files
COPY . /var/www/html/

# Rename numbered files to their proper web names
RUN cp /var/www/html/02_config.php    /var/www/html/config.php
RUN cp /var/www/html/03_save_lead.php /var/www/html/save_lead.php
RUN cp /var/www/html/04_login.php     /var/www/html/login.php
RUN cp /var/www/html/05_admin.php     /var/www/html/admin.php
RUN cp /var/www/html/06_logout.php    /var/www/html/logout.php
RUN cp /var/www/html/07_index.php     /var/www/html/index.php

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
