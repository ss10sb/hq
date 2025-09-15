FROM php:8.4-cli-alpine

RUN apk add --no-cache --virtual .build-deps freetype-dev libjpeg-turbo-dev libpng-dev libzip-dev $PHPIZE_DEPS linux-headers && \
    apk add --no-cache libzip git freetype libjpeg-turbo libpng && \
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd && \
    docker-php-ext-install pcntl mysqli pdo pdo_mysql zip && \
    docker-php-ext-enable zip && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apk del .build-deps

VOLUME ["/app"]

WORKDIR /app

ENTRYPOINT ["/bin/sh"]
