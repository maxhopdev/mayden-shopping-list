#!/bin/sh
set -e

# First-run setup so a fresh copy works with just `docker compose up`.
if [ ! -f .env ]; then
    echo "Creating .env from .env.example"
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer dependencies"
    composer install --no-interaction --prefer-dist
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    echo "Generating app key"
    php artisan key:generate --force
fi

php artisan config:clear
php artisan migrate --force

exec "$@"
