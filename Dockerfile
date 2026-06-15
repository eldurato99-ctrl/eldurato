FROM php:8.2-apache

# Required packages install
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    libzip-dev

# PHP extensions install
RUN docker-php-ext-install pdo pdo_mysql mysqli zip

# Apache rewrite enable (.htaccess support)
RUN a2enmod rewrite

# Apache document root permissions
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
