#!/bin/sh
set -e

APP_PATH="/var/www/html"
RUNTIME_PATH="${APP_PATH}/runtime"

if [ ! -f "${APP_PATH}/vendor/autoload.php" ]; then
    echo "Composer 依赖缺失，正在安装..."
    composer install \
        --working-dir="${APP_PATH}" \
        --no-interaction \
        --prefer-dist \
        --no-dev \
        --optimize-autoloader
fi

mkdir -p \
    "${RUNTIME_PATH}/view/cache" \
    "${RUNTIME_PATH}/view/compile" \
    "${RUNTIME_PATH}/log" \
    "${RUNTIME_PATH}/waf/PACKET"

chown -R www-data:www-data "${RUNTIME_PATH}"

exec docker-php-entrypoint "$@"
