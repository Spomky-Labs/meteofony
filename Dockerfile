#syntax=docker/dockerfile:1

FROM dunglas/frankenphp:builder AS frankenphp_builder
LABEL builder=true

COPY --from=caddy:builder /usr/bin/xcaddy /usr/bin/xcaddy

ENV GOTOOLCHAIN=auto

# CGO must be enabled to build FrankenPHP
RUN CGO_ENABLED=1 \
	XCADDY_SETCAP=1 \
	XCADDY_GO_BUILD_FLAGS="-ldflags='-w -s' -tags=nobadger,nomysql,nopgx" \
	CGO_CFLAGS=$(php-config --includes) \
	CGO_LDFLAGS="$(php-config --ldflags) $(php-config --libs)" \
	xcaddy build \
		--output /usr/local/bin/frankenphp \
		--with github.com/dunglas/frankenphp=./ \
		--with github.com/dunglas/frankenphp/caddy=./caddy/ \
		--with github.com/dunglas/caddy-cbrotli \
		# Mercure and Vulcain are included in the official build, but feel free to remove them
		--with github.com/dunglas/mercure/caddy \
		--with github.com/dunglas/vulcain/caddy \
		# Add extra Caddy modules here
		--with github.com/corazawaf/coraza-caddy/v2

FROM dunglas/frankenphp:1 AS frankenphp_runner
LABEL builder=true

# Replace the official binary by the one contained your custom modules
COPY --from=frankenphp_builder /usr/local/bin/frankenphp /usr/local/bin/frankenphp


# Versions
FROM frankenphp_runner AS frankenphp_upstream
LABEL builder=true

# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base
LABEL builder=true

WORKDIR /app

VOLUME /app/var/

# persistent / runtime deps
# hadolint ignore=DL3008
RUN apt-get update && apt-get install -y --no-install-recommends \
	acl \
	file \
	gettext \
	git \
	&& rm -rf /var/lib/apt/lists/*

RUN set -eux; \
	install-php-extensions \
		@composer \
		apcu \
		intl \
		opcache \
		zip \
		pdo_pgsql \
		gmp \
		gd \
		imagick \
		amqp \
		fileinfo \
		iconv \
		exif \
		gettext \
		sodium \
		opcache \
		redis \
		uuid \
		xsl \
		xml \
		zip \
		brotli \
		zstd \
	;

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Transport to use by Mercure (default to Bolt)
ENV MERCURE_TRANSPORT_URL=bolt:///data/mercure.db

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

###> recipes ###
###> doctrine/doctrine-bundle ###
RUN install-php-extensions pdo_pgsql
###< doctrine/doctrine-bundle ###
###< recipes ###

COPY --link frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link frankenphp/Caddyfile /etc/caddy/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]

# Dev FrankenPHP image
FROM frankenphp_base AS frankenphp_dev
LABEL builder=false

ENV APP_ENV=dev XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN set -eux; \
	install-php-extensions \
		xdebug \
	;

COPY --link frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]

# Prod FrankenPHP image
FROM frankenphp_base AS frankenphp_prod
LABEL builder=false

ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="import worker.Caddyfile"

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/
COPY --link frankenphp/worker.Caddyfile /etc/caddy/worker.Caddyfile

# prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.* symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# copy sources
COPY --link . ./
RUN rm -Rf frankenphp/

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	npm install && npm run build; \
	chmod +x bin/console; sync; \
	bin/console importmap:install --no-interaction; \
	bin/console tailwind:build; \
	bin/console asset-map:compile;
