#!/usr/bin/env bash
set -euo pipefail

cd /app

case "${1:-}" in
    app)
        composer install --no-interaction --prefer-dist
        mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache

        echo "Waiting for database at ${DB_HOST}:${DB_PORT}..."
        until mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; do
            sleep 2
        done

        php artisan migrate --force
        exec php artisan serve --host=0.0.0.0 --port=8000
        ;;
    vite)
        npm install --ignore-scripts
        exec npm run dev -- --host 0.0.0.0 --port 5173
        ;;
    *)
        exec "$@"
        ;;
esac
