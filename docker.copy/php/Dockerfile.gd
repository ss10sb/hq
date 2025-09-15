FROM public.ecr.aws/phoenixcollege/laravel-php-fpm-mssql:8.4

ARG WWW_USER=www-data
ARG WWW_GROUP=www-data
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG XDEBUG_INSTALL=0

RUN \
  apk add --no-cache --virtual .persist-deps wget curl mariadb-client shadow bash freetype libjpeg-turbo libpng && \
  apk add --no-cache --virtual .build-deps freetype-dev libjpeg-turbo-dev libpng-dev $PHPIZE_DEPS linux-headers && \
  docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg && \
  docker-php-ext-install -j$(nproc) gd

RUN \
  if [ "$XDEBUG_INSTALL" = 1 ]; then \
  echo "Installing Xdebug" && \
  pecl install xdebug; \
  fi

RUN apk del -f .build-deps

# PHP module configs
COPY ./conf/mods-available/ "$PHP_INI_DIR/conf.d/"

RUN \
   if [ "$XDEBUG_INSTALL" = 0 ]; then \
    rm "$PHP_INI_DIR/conf.d/99-xdebug.ini"; \
   fi

COPY ./conf/zz-overrides-dev.ini "$PHP_INI_DIR/conf.d/zz-overrides-dev.ini"

COPY ./conf/mariadb-ssl.cnf /etc/my.cnf.d/99-mariadb-ssl.cnf

RUN \
   groupmod -g ${GROUP_ID} ${WWW_GROUP} && \
   usermod -u ${USER_ID} ${WWW_USER} && \
   chown -R ${USER_ID}:${GROUP_ID} /app && \
   chmod -R 0775 /app

# XDebug port
EXPOSE 9003

VOLUME /app
WORKDIR /app
