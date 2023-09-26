FROM dunglas/frankenphp:latest as frankenphp

RUN install-php-extensions \
    pdo_pgsql \
    apcu \
    opcache \
    gd \
    intl \
    zip

COPY . /app/public

SHELL ["/bin/bash", "-o", "pipefail", "-c"]
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
