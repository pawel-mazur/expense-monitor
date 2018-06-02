FROM php:7.2-apache

RUN apt-get update && apt-get install -y libpq-dev git && \
    pecl install xdebug

RUN docker-php-ext-install pdo_pgsql && \
    docker-php-ext-enable opcache

# Configure Apache
ARG APACHE_DOCUMENT_ROOT="/var/www/html/web"

RUN sed -ri -e "s!/var/www/html!$APACHE_DOCUMENT_ROOT!g" /etc/apache2/sites-available/*.conf && \
    sed -ri -e "s!/var/www/!$APACHE_DOCUMENT_ROOT!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    a2enmod rewrite

# Configure PHP
COPY docker/web/xdebug.ini /usr/local/etc/php/mods-available/xdebug.ini

# Copy sources
COPY . /var/www/html

# Permission
RUN chown -RL www-data:www-data var/cache var/logs var/sessions var/imports
