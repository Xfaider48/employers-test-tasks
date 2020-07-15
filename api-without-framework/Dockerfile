FROM php:7.4-fpm-alpine

WORKDIR /app

RUN apk --no-cache add postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

