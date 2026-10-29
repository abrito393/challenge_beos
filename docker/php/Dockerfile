FROM php:8.3-fpm-alpine

ARG UID=1000
ARG GID=1000

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache fcgi git unzip \
    && install-php-extensions bcmath intl opcache pcov pdo_mysql zip

# Align www-data with the host user so bind-mounted files keep sane ownership.
RUN apk add --no-cache --virtual .user-deps shadow \
    && usermod -o -u "${UID}" www-data \
    && groupmod -o -g "${GID}" www-data \
    && apk del .user-deps

COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY fpm-pool.conf /usr/local/etc/php-fpm.d/zz-app.conf

WORKDIR /var/www/html

USER www-data

HEALTHCHECK --interval=10s --timeout=3s --retries=5 \
    CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1
