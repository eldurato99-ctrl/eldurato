# ========================================================
# STAGE 1: Dependencies Download Karne Ke Liye (Composer)
# ========================================================
FROM composer:latest AS vendor

# Working directory set karein
WORKDIR /app

# Pehle sirf composer files copy karein cache optimize karne ke liye
COPY composer.json composer.lock ./

# Saari dependencies download karein bina dev packages ke
RUN composer install --no-dev --ignore-platform-reqs --optimize-autoloader

# ========================================================
# STAGE 2: Actual Application Run Karne Ke Liye (Apache)
# ========================================================
FROM php:8.2-apache

# Linux tools aur extensions install karein
RUN apt-get update && apt-get install -y \
    zip unzip git libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions configure aur install karein
RUN docker-php-ext-install pdo pdo_mysql mysqli zip

# Apache rewrite module enable karein
RUN a2enmod rewrite

# Apache custom configuration copy karein
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Pure project ka code copy karein
COPY . /var/www/html/

# STAGE 1 se download kiya hua vendor folder yahan copy karein
# Yeh line aapka Google API client wala error fixed karegi!
COPY --from=vendor /app/vendor /var/www/html/vendor

# Permissions sahi karein taaki Apache files read/write kar sake
RUN chown -R www-data:www-data /var/www/html

# Port expose karein
EXPOSE 80
