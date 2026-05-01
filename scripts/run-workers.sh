#!/usr/bin/env bash
set -euo pipefail

php artisan queue:work redis --sleep=3 --tries=3 --timeout=120
