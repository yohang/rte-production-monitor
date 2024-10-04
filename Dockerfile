ARG PHP_VERSION=8.3
ARG NODE_VERSION=22
ARG FRANKENPHP_VERSION=1.2
ARG ALPINE_VERSION=3.20

FROM node:${NODE_VERSION}-alpine${ALPINE_VERSION} AS node

FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}-alpine AS app

ARG EXTERNAL_USER_ID=1000

ENV SERVER_NAME=:80

RUN set -eux; \
    apk add --no-cache sqlite; \
    install-php-extensions zip pdo_pgsql pcntl opcache intl mbstring apcu; \
    sync

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/include/node /usr/local/include/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /opt/yarn* /opt/yarn


RUN ln -vs /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm
RUN ln -vs /opt/yarn/bin/yarn /usr/local/bin/yarn

COPY --chown=www-data:www-data infra/docker/php/Caddyfile /etc/caddy/Caddyfile
COPY --chown=www-data:www-data infra/docker/php/docker-entrypoint /usr/local/bin/docker-entrypoint

RUN chmod a+x /usr/local/bin/docker-entrypoint

RUN set -eux; \
    echo "Setting User id (external): ${EXTERNAL_USER_ID}"; \
    sed -i -r s/"(www-data:x:)([[:digit:]]+):([[:digit:]]+):"/\\1${EXTERNAL_USER_ID}:${EXTERNAL_USER_ID}:/g /etc/passwd; \
    sed -i -r s/"(www-data:x:)([[:digit:]]+):"/\\1${EXTERNAL_USER_ID}:/g /etc/group; \
    mkdir -p /var/run/php /app/var/data; \
    chown -R www-data:www-data /app /var/www /usr/local/etc/php /var/run/php /home/www-data /config /data /app/var

USER www-data

ENV APP_ENV=prod
ENV APP_DEBUG=false

WORKDIR /app

COPY --chown=www-data:www-data composer.json composer.lock symfony.lock ./
RUN set -eux; \
    composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress; \
    composer clear-cache; \
    mkdir -p var

COPY --chown=www-data:www-data bin bin/
COPY --chown=www-data:www-data config config/
COPY --chown=www-data:www-data migrations migrations/
COPY --chown=www-data:www-data public public/
COPY --chown=www-data:www-data src src/
COPY --chown=www-data:www-data templates templates/
COPY --chown=www-data:www-data translations translations/

COPY assets assets/

RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer install --prefer-dist --no-dev --no-progress; \
    composer dump-autoload --optimize --no-dev --classmap-authoritative; \
    php bin/console cache:clear; \
    php bin/console cache:warmup -eprod; \
    chmod +x bin/console; \
    sync


EXPOSE 80
EXPOSE 443
EXPOSE 443/udp

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1

ENTRYPOINT ["docker-entrypoint"]

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]
