FROM public.ecr.aws/phoenixcollege/laravel-php-fpm:8.4

ARG WWW_USER=www-data
ARG WWW_GROUP=www-data
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG XDEBUG_INSTALL=0

RUN \
  apk add --no-cache --virtual .persist-deps wget curl postgresql-client shadow bash && \
  apk add --nocache --virtual .build-deps postgresql-dev && \
  docker-php-ext-install pdo_pgsql

RUN \
  if [ "$XDEBUG_INSTALL" = 1 ]; then \
  echo "Installing Xdebug" && \
  apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers && \
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

RUN \
   groupmod -g ${GROUP_ID} ${WWW_GROUP} && \
   usermod -u ${USER_ID} ${WWW_USER} && \
   chown -R ${USER_ID}:${GROUP_ID} /app && \
   chmod -R 0775 /app

# XDebug port
EXPOSE 9003

VOLUME /app
WORKDIR /app
