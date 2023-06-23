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

RUN curl -sS https://download.newrelic.com/php_agent/release/newrelic-php5-10.11.0.3-linux-musl.tar.gz | tar -C /tmp -zx && \
    export NR_INSTALL_USE_CP_NOT_LN=1 && \
    export NR_INSTALL_SILENT=1 && \
    /tmp/newrelic-php5-*/newrelic-install install && \
    rm -rf /tmp/newrelic-php5-* /tmp/nrinstall* && \
    sed -i \
        -e 's/"REPLACE_WITH_REAL_KEY"/\${NEW_RELIC_AGENT_LICENSE_KEY}/' \
        -e 's/newrelic.appname = "PHP Application"/newrelic.appname = \${NEW_RELIC_APP_NAME}/' \
        -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
        -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
        -e 's/;newrelic.daemon.address =.*/newrelic.daemon.address=\${NEW_RELIC_DAEMON_ADDRESS}/' \
        /etc/php82/conf.d/newrelic.ini

USER www-data
WORKDIR '/var/www/habit'

COPY --chown=www-data:www-data . .

ENV APP_ENV=prod

RUN composer install --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-scripts

CMD ["php-fpm82", "-F"]

EXPOSE 9001
