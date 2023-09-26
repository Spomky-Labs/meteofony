FROM dunglas/frankenphp:latest as frankenphp

RUN install-php-extensions \
    pdo_pgsql \
    xdebug \
    apcu \
    opcache \
    gd \
    intl \
    zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
