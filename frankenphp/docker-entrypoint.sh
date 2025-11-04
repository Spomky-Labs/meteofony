#!/bin/sh
set -e

echo "🚀 Entrypoint script started with argument: $1"

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	echo "✅ Recognized command: $1"

	# Install the project the first time PHP is started
	# After the installation, the following block can be deleted
	if [ ! -f composer.json ]; then
		echo "📁 No composer.json found, installing Symfony skeleton..."

		rm -Rf tmp/
		echo "🧹 Removed tmp/ directory"

		composer create-project "symfony/skeleton $SYMFONY_VERSION" tmp --stability="$STABILITY" --prefer-dist --no-progress --no-interaction --no-install
		echo "📦 Symfony skeleton created in tmp/"

		cd tmp
		cp -Rp . ..
		echo "📂 Copied skeleton contents to project root"
		cd -
		rm -Rf tmp/
		echo "🧹 Cleaned up tmp/"

		composer require "php:>=$PHP_VERSION" runtime/frankenphp-symfony
		echo "🔧 Required PHP and runtime/frankenphp-symfony"

		composer config --json extra.symfony.docker 'true'
		echo "⚙️ Symfony Docker config set"

		if grep -q ^DATABASE_URL= .env; then
			echo "⏸️ DATABASE_URL found in .env. Waiting for manual step..."
			echo "To finish the installation please press Ctrl+C to stop Docker Compose and run: docker compose up --build -d --wait"
			sleep infinity
		fi
	fi

	if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
		echo "📦 vendor/ is empty. Installing dependencies..."
		composer install --prefer-dist --no-progress --no-interaction
	fi

	if [ "$APP_ENV" = "prod" ]; then
		echo "🔍 Production environment detected"

		if [ -d vendor/spomky-labs/pwa-bundle ]; then
			echo "🗺️ Running pwa:compile..."
			php bin/console pwa:compile
		fi
	fi

	if grep -q ^DATABASE_URL= .env; then
		echo "⏳ Waiting for database to be ready..."
		ATTEMPTS_LEFT_TO_REACH_DATABASE=60
		until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
			if [ $? -eq 255 ]; then
				# If the Doctrine command exits with 255, an unrecoverable error occurred
				ATTEMPTS_LEFT_TO_REACH_DATABASE=0
				break
			fi
			sleep 1
			ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
			echo "⌛ Still waiting for database... $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left."
		done

		if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
			echo "❌ The database is not up or not reachable:"
			echo "$DATABASE_ERROR"
			exit 1
		else
			echo "✅ The database is now ready and reachable"
		fi

		if [ "$( find ./migrations -iname '*.php' -print -quit )" ]; then
			echo "📜 Running doctrine:migrations:migrate..."
			php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
		fi

		#echo "📜 Running geoip2:update..."
		#php bin/console geoip2:update

		#echo "📜 Running app:browscap:update..."
		#php bin/console app:browscap:update
	fi

	echo "🔐 Setting permissions on var/"
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var
fi

echo "🚚 Executing docker-php-entrypoint with: $*"
exec docker-php-entrypoint "$@"
