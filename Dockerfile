FROM php:8.2-fpm

# Instalacja zależności systemowych i brakujących rozszerzeń PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql intl

# Instalacja rozszerzenia Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instalacja Composera
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ustawienie katalogu roboczego
WORKDIR /var/www/html

# Instalacja zależności aplikacji
RUN composer install --no-dev --prefer-dist