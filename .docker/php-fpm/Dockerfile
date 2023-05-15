FROM alpine:3.18

RUN apk add --no-cache \
    nano\
    coreutils \
    gettext \
    php82-fpm \
    php82-ctype \
    php82-curl \
    php82-dom \
    php82-gd \
    php82-iconv \
    php82-json \
    php82-intl \
    php82-fileinfo\
    php82-mbstring \
    php82-opcache \
    php82-openssl \
    php82-pdo \
    php82-pdo_pgsql \
    php82-pgsql \
    php82-xml \
    php82-xsl \
    php82-zlib \
    php82-phar \
    php82-tokenizer \
    php82-session \
    php82-simplexml \
    php82-zip \
    php82-xmlwriter \
    php82-sodium \
    php82-pecl-apcu \
    php82-redis \
    make \
    curl

RUN set -x ; \
  addgroup -g 82 -S www-data ; \
  adduser -u 82 -D -S -G www-data www-data && exit 0 ; exit 1

RUN curl -sS https://getcomposer.org/installer | tee composer-setup.php \
    && php82 composer-setup.php && rm composer-setup.php* \
    && chmod +x composer.phar && mv composer.phar /usr/bin/composer \
    && ln -s /usr/bin/php82 /usr/local/bin/php

COPY ./.docker/php-fpm/symfony.ini /etc/php82/conf.d/
COPY ./.docker/php-fpm/symfony.ini /etc/php82/cli/conf.d/

COPY ./.docker/php-fpm/symfony.pool.conf /etc/php82/php-fpm.d/

USER www-data
WORKDIR '/var/www/habit'

COPY --chown=www-data:www-data . .

ENV APP_ENV=prod

RUN composer install --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-scripts

CMD ["php-fpm82", "-F"]

EXPOSE 9001
