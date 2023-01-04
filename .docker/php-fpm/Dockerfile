FROM alpine:3.17

RUN apk add --no-cache \
    nano\
    coreutils \
    gettext \
    php81-fpm \
    php81-ctype \
    php81-curl \
    php81-dom \
    php81-gd \
    php81-iconv \
    php81-json \
    php81-intl \
    php81-fileinfo\
    php81-mbstring \
    php81-opcache \
    php81-openssl \
    php81-pdo \
    php81-pdo_pgsql \
    php81-pgsql \
    php81-xml \
    php81-xsl \
    php81-zlib \
    php81-phar \
    php81-tokenizer \
    php81-session \
    php81-simplexml \
    php81-zip \
    php81-xmlwriter \
    php81-sodium \
    php81-pecl-apcu \
    php81-redis \
    make \
    curl

RUN set -x ; \
  addgroup -g 82 -S www-data ; \
  adduser -u 82 -D -S -G www-data www-data && exit 0 ; exit 1

RUN curl -sS https://getcomposer.org/installer | tee composer-setup.php \
    && php81 composer-setup.php && rm composer-setup.php* \
    && chmod +x composer.phar && mv composer.phar /usr/bin/composer \
    && ln -s /usr/bin/php81 /usr/local/bin/php

COPY ./.docker/php-fpm/symfony.ini /etc/php81/conf.d/
COPY ./.docker/php-fpm/symfony.ini /etc/php81/cli/conf.d/

COPY ./.docker/php-fpm/symfony.pool.conf /etc/php81/php-fpm.d/

USER www-data
WORKDIR '/var/www/habit'

COPY --chown=www-data:www-data . .

ENV APP_ENV=prod

RUN composer install --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-scripts

CMD ["php-fpm81", "-F"]

EXPOSE 9001
