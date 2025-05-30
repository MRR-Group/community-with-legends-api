# For other versions check: https://hub.docker.com/_/composer/tags
FROM composer/composer:2.8.6-bin@sha256:989ad47cea06cb9365afda43d7431a238c6abf913d631b9cf043eb0d43f783ae AS composer-bin

# For other versions check: https://hub.docker.com/_/php/tags?page=1&name=fpm-bullseye
FROM php:8.3.12-fpm-bullseye@sha256:b9b5efd7bfe05e65097e8253b038c4b711798d95f49c89e4337c0b330f9fc8ff

COPY --from=composer-bin ./composer /usr/bin/composer

ARG USER_NAME=host-user
ARG USER_ID=1000
ARG PHP_FPM_GROUP=www-data

RUN adduser \
        --disabled-password \
        --uid ${USER_ID} \
        ${USER_NAME} \
    && usermod \
        --append \
        --groups \
        ${PHP_FPM_GROUP} \
        ${USER_NAME}

# Use the default development configuration
RUN mv "${PHP_INI_DIR}/php.ini-development" "${PHP_INI_DIR}/php.ini"

# For other versions check: http://nginx.org/packages/mainline/debian/pool/nginx/n/nginx/
ARG NGINX_VERSION="1.25.5-1~bullseye"
# For other versions check: https://pecl.php.net/package/redis
ARG PHPREDIS_VERSION=6.0.2

RUN apt-get update \
    && apt-get install --assume-yes gpg \
    && curl https://nginx.org/keys/nginx_signing.key | gpg --dearmour --output /etc/apt/trusted.gpg.d/apt.nginx.org.gpg > /dev/null \
    && echo "deb https://nginx.org/packages/mainline/debian bullseye nginx" | tee /etc/apt/sources.list.d/nginx.list \
    && apt-get update && apt-get install --assume-yes \
        nginx=${NGINX_VERSION} \
        libzip-dev \
        libpq-dev \
        libpng-dev \
        supervisor \
        libmagickwand-dev \
        cron \
    && pecl install redis-${PHPREDIS_VERSION} \
    && pecl install imagick \
    && docker-php-ext-install zip gd pdo_pgsql \
    && docker-php-ext-enable redis \
    && docker-php-ext-enable imagick

# For other versions check: https://pecl.php.net/package/xdebug
ARG XDEBUG_VERSION=3.3.2
ARG INSTALL_XDEBUG=true

RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    pecl install xdebug-${XDEBUG_VERSION} \
    && docker-php-ext-enable xdebug \
;fi

COPY ./nginx.conf /nginx.conf
COPY ./nginx_ssl.conf /nginx_ssl.conf

ARG ENABLE_SSL=false

RUN if [ ${ENABLE_SSL} = true ]; then \
        cp /nginx_ssl.conf /etc/nginx/nginx.conf; \
    else \
        cp /nginx.conf /etc/nginx/nginx.conf; \
fi

COPY ./entrypoint.sh /entrypoint.sh

WORKDIR /application

ENTRYPOINT ["/entrypoint.sh"]
