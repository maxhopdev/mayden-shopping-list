FROM php:8.4-fpm

ARG UID=1000
ARG GID=1000

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip sqlite3 \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions pdo_mysql pdo_sqlite bcmath intl zip gd pcntl opcache

COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY --chmod=755 entrypoint.sh /usr/local/bin/entrypoint.sh

# Run as the host user's UID/GID so files created in the container stay editable on the host.
RUN groupmod -o -g ${GID} www-data && usermod -o -u ${UID} -g ${GID} www-data

WORKDIR /var/www/html

USER www-data

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
