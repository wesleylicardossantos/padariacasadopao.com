#!/usr/bin/env bash
set -euo pipefail

EMPRESA_ID=${1:-1}
php artisan financeiro:audit "$EMPRESA_ID" || true
php artisan saas:snapshot-usage "$EMPRESA_ID" || true

echo 'Warmup enterprise finalizado.'
