FROM alpine:3.15

RUN apk add --no-cache \
    nano\
    coreutils \
    gettext \
    php8-fpm \
    php8-ctype \
    php8-curl \
    php8-dom \
    php8-gd \
    php8-iconv \
    php8-json \
    php8-intl \
    php8-fileinfo\
    php8-mbstring \
    php8-opcache \
    php8-openssl \
    php8-pdo \
    php8-pdo_pgsql \
    php8-pgsql \
    php8-xml \
    php8-xsl \
    php8-zlib \
    php8-phar \
    php8-tokenizer \
    php8-session \
    php8-simplexml \
    php8-zip \
    php8-xmlwriter \
    php8-sodium \
    php8-pecl-apcu \
    php8-redis \
    make \
    curl

RUN set -x ; \
  addgroup -g 82 -S www-data ; \
  adduser -u 82 -D -S -G www-data www-data && exit 0 ; exit 1

RUN curl -sS https://getcomposer.org/installer | tee composer-setup.php \
    && php8 composer-setup.php && rm composer-setup.php* \
    && chmod +x composer.phar && mv composer.phar /usr/bin/composer \
    && ln -s /usr/bin/php8 /usr/local/bin/php

COPY ./docker/php-fpm/symfony.ini /etc/php8/conf.d/
COPY ./docker/php-fpm/symfony.ini /etc/php8/cli/conf.d/

COPY ./docker/php-fpm/symfony.pool.conf /etc/php8/php-fpm.d/

USER www-data
WORKDIR '/var/www/habit'

COPY --chown=www-data:www-data . .

ENV APP_ENV=prod

RUN composer install --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-scripts

CMD ["php-fpm8", "-F"]

EXPOSE 9001