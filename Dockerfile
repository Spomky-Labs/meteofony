FROM dunglas/frankenphp as frankenphp

RUN install-php-extensions \
    pdo_pgsql \
    xdebug \
    apcu \
    opcache \
    gd \
    intl \
    zip \
