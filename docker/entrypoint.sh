#!/bin/sh
set -e

# Wait for the database to be ready (optional, but good practice)
# This is a simple loop; a more robust solution might use dockerize or wait-for-it.sh
# The DB_HOST is expected to be set as an environment variable
# echo "Waiting for database host ${DB_HOST}..."
# while ! nc -z "${DB_HOST}" "${DB_PORT:-3306}"; do
#   sleep 1
# done
# echo "Database is up."

# Run Composer Install if vendor directory doesn't exist or is empty
if [ ! -d "vendor" ] || [ -z "$(ls -A vendor)" ]; then
  echo "Vendor directory not found or empty. Running composer install..."
  composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
else
  echo "Vendor directory exists. Skipping composer install."
fi

# Generate app key if it doesn't exist (check .env or a dedicated marker)
if [ ! -f ".env" ]; then
    echo "Copying .env.example to .env"
    cp .env.example .env
fi

# Ensure APP_KEY is set
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not set. Generating key..."
    php artisan key:generate --force
else
    echo "APP_KEY is set."
fi


# Run database migrations
echo "Running database migrations..."
php artisan migrate --force # --force is recommended for production to avoid prompts

# Optimize Laravel (config, routes, views)
echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions after composer install and other artisan commands
# This ensures www-data (user running php-fpm/nginx) can write to storage/logs
echo "Setting permissions for storage and bootstrap/cache..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Entrypoint tasks complete. Starting supervisord..."

# Execute the CMD from Dockerfile (supervisord)
exec "$@"
