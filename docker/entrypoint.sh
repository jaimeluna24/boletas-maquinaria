#!/bin/sh
set -e

# El volumen de storage puede llegar vacío
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
php artisan storage:link --force || true

# Solo el contenedor web migra (evita carreras con worker/scheduler)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force
fi

php artisan optimize

exec "$@"
