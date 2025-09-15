FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    zip libzip-dev libpng-dev libjpeg-dev libjpeg62-turbo-dev libfreetype6-dev zlib1g-dev
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
RUN docker-php-ext-configure zip && \
    docker-php-ext-install -j$(nproc) gd pcntl zip mysqli pdo pdo_mysql && \
    docker-php-ext-enable gd zip && \
    a2enmod rewrite

COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf
RUN sed -i "s/Listen 80/Listen 8000/g" /etc/apache2/ports.conf

VOLUME ["/app"]

WORKDIR /app

COPY ./web.entrypoint.sh /entrypoint.sh

RUN groupmod -g 1000 www-data && \
    usermod -u 1000 www-data && \
    chown -R www-data:www-data /app && \
    chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
