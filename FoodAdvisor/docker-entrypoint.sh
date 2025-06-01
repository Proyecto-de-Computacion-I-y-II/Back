#!/bin/sh
set -e

until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
  echo "Esperando a la base de datos..."
  sleep 2
done

if [ ! -f /var/www/.env ] || ! grep -q '^APP_KEY=' /var/www/.env; then
  echo "Generando clave APP_KEY..."
  php artisan key:generate
fi


php artisan migrate --force


exec php -S 0.0.0.0:8000 -t public
