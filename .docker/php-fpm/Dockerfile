FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    nano \
    coreutils \
    gettext \
    icu-dev \
    libpq-dev \
    libxslt-dev \
    libzip-dev \
    libsodium-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    linux-headers \
    autoconf \
    gcc \
    g++ \
    make \
    curl \
    openssl \
    patch \
    git

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo_pgsql \
        pgsql \
        xsl \
        zip \
        gd

RUN pecl install apcu && docker-php-ext-enable apcu
RUN pecl install redis && docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

COPY ./.docker/php-fpm/symfony.ini /usr/local/etc/php/conf.d/
COPY ./.docker/php-fpm/symfony.pool.conf /usr/local/etc/php-fpm.d/symfony.pool.conf
RUN rm -f /usr/local/etc/php-fpm.d/www.conf* /usr/local/etc/php-fpm.d/docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

RUN curl -sS https://download.newrelic.com/php_agent/archive/12.6.0.34/newrelic-php5-12.6.0.34-linux-musl.tar.gz | tar -C /tmp -zx && \
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
        /usr/local/etc/php/conf.d/newrelic.ini

USER www-data
WORKDIR '/var/www/habit'

COPY --chown=www-data:www-data . .

ENV APP_ENV=prod

RUN composer install --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --no-scripts

CMD ["php-fpm"]