#!/bin/sh
set -eu

php artisan down || true
php artisan migrate --force
php artisan system:healthcheck --write || true
php artisan system:safe-optimize || true
php artisan up || true

echo "Hardening aplicado. Validar storage/app/healthcheck.json e storage/logs/laravel.log"
