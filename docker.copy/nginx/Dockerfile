FROM nginx:mainline-alpine

ARG NGINX_GROUP=nginx
ARG GROUP_ID=1000

RUN apk --no-cache add shadow && \
    groupmod -g ${GROUP_ID} ${NGINX_GROUP}

COPY ./conf/ /etc/nginx/

RUN rm -f /etc/nginx/conf.d/default

VOLUME /var/log/nginx

EXPOSE 443
