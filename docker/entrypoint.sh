#!/bin/sh
# Prepares the container on every start (as www-data): database schema, demo data (first start only), caches, then Apache.
set -e

php artisan migrate --force

# Seed the demo dataset once; the marker lives in the storage volume, so restarts keep the data.
if [ "${SEED_DEMO_DATA:-true}" = "true" ] && [ ! -f storage/app/.seeded ]; then
    php artisan db:seed --force
    touch storage/app/.seeded
fi

php artisan optimize

exec "$@"
