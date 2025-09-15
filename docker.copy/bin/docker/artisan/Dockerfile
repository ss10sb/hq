ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli-alpine

RUN apk add --no-cache --virtual .build-deps libzip-dev $PHPIZE_DEPS \
    && apk add --no-cache libzip \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && apk del .build-deps
