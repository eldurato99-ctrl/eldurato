FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    zip unzip git libzip-dev

RUN docker-php-ext-install pdo pdo_mysql mysqli zip

RUN a2enmod rewrite

COPY apache.conf /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
