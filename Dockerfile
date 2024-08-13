ARG PHP_VERSION=8.1

FROM php:${PHP_VERSION}-fpm-alpine AS php_cli

ENV TZ Europe/Berlin

RUN set -ex && \
    apk update && \
    apk upgrade && \
    apk add --no-cache tzdata && \
    cp /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone && \
    apk del tzdata && \
    rm -rf /tmp/* /var/cache/apk/*;

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apk add --no-cache git openssh-client

VOLUME /var/run/php

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /srv/app

COPY . .

CMD ["php-fpm"]
