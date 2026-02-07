## Build stage

FROM node:25-alpine AS build-assets

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY tailwind.config.js ./
COPY assets ./assets
COPY site ./site

RUN npm run build

## run stage

FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd zip intl mbstring opcache
RUN a2enmod rewrite
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

COPY . .
COPY --from=build-assets /app/assets/css/tailwind.css ./assets/css/tailwind.css

RUN mkdir -p site/accounts site/cache site/sessions media content
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

VOLUME /var/www/html/content

EXPOSE 80

CMD ["apache2-foreground"]
