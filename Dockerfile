FROM php:8.2-apache

# Enable mysqli extension
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy all project files to Apache web root
COPY . /var/www/html/

# Rename 07_index.php to index.php as the main entry point
RUN cp /var/www/html/07_index.php /var/www/html/index.php

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
