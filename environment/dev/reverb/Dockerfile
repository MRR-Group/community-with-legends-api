FROM php:8.3.12-fpm-bullseye@sha256:b9b5efd7bfe05e65097e8253b038c4b711798d95f49c89e4337c0b330f9fc8ff

WORKDIR /application

RUN docker-php-ext-configure pcntl --enable-pcntl \
  && docker-php-ext-install \
    pcntl

EXPOSE 8080

CMD [ "php", "artisan", "reverb:start", "--host=0.0.0.0" ]
