FROM php:8.2-fpm

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

RUN docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./app /var/www/html

WORKDIR /var/www/html

RUN composer install --no-dev --prefer-dist