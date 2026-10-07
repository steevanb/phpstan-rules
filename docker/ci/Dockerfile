FROM php:8.5.10-cli-alpine3.24

COPY --from=composer:2.10.3 /usr/bin/composer /usr/local/bin/composer

ENV PHPSTAN_BIN_PATH=/usr/local/bin/phpstan
ENV COMPOSER_HOME=/composer

COPY composer.json ${COMPOSER_HOME}/composer.json

RUN \
    apk add --no-cache python3 zip git icu-dev icu-data-full bzip2-dev \
    && docker-php-ext-install bcmath bz2 intl pcntl \
    && apk add --no-cache --virtual .pcov-build-deps ${PHPIZE_DEPS} \
    && pecl install pcov-1.0.12 \
    && docker-php-ext-enable pcov \
    && apk del .pcov-build-deps \
    && composer global up \
    && ln -s ${COMPOSER_HOME}/vendor/bin/composer-require-checker /usr/local/bin/composer-require-checker \
    && ln -s ${COMPOSER_HOME}/vendor/bin/phpcs /usr/local/bin/phpcs \
    && ln -s ${COMPOSER_HOME}/vendor/bin/phpcbf /usr/local/bin/phpcbf \
    && ln -s ${COMPOSER_HOME}/vendor/bin/phpstan /usr/local/bin/phpstan \
    && wget https://github.com/wapmorgan/PhpDeprecationDetector/releases/download/2.0.33/phpdd-2.0.33.phar -O /usr/local/bin/phpdd \
    && chmod +x /usr/local/bin/phpdd \
    && rm -rf /tmp/*

COPY php.ini /usr/local/etc/php/conf.d/99-php.ini

ENV COMPOSER_CACHE_DIR=/app/var/composer

WORKDIR /app
