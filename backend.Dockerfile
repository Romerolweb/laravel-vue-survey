# Use an official FrankenPHP base image with PHP 8.2 (Debian variant recommended by FrankenPHP docs)
FROM dunglas/frankenphp:1-php8.2-bookworm as base

# Set working directory
WORKDIR /app

# Install system dependencies needed for extensions or tools
# Debian uses 'apt-get'
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    zip \
    # For gd extension
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    # For intl extension
    libicu-dev \
    # For other common extensions or build tools if necessary
    libzip-dev \
    # libpq-dev # For PostgreSQL if needed in future
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions using the provided helper script in dunglas/frankenphp images
# Or use docker-php-ext-install if that script is not present/preferred
RUN install-php-extensions \
    opcache \
    pdo_mysql \
    gd \
    intl \
    zip \
    bcmath \
    exif \
    mbstring \
    tokenizer \
    xml \
    sockets # Often useful for Octane or other tools

# Install Composer globally
# Pin to specific version to avoid supply chain risks
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Copy application code
# We'll copy specific files first for better layer caching with composer
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader \
    && composer clear-cache

# Copy the rest of the application code
COPY . .

# Install Laravel Octane and configure for FrankenPHP
# This runs within the PHP 8.2 environment of the Docker build
RUN php artisan octane:install --server=frankenphp --no-interaction

# Set permissions for storage and bootstrap/cache
# FrankenPHP/Caddy runs as root by default in the container, then can drop privileges.
# Or, you might run php artisan commands as a specific user.
# For simplicity, ensure these are writable. The entrypoint will handle this.
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Environment variables for Laravel (can be overridden in docker-compose)
# These are illustrative; primary config should be via .env in docker-compose
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=mysql \
    DB_HOST=db \
    DB_PORT=3306 \
    OCTANE_SERVER=frankenphp \
    FRANKENPHP_CONFIG="worker /app/public/index.php" # Basic worker mode if not using Octane command

# Expose port 80 (FrankenPHP/Caddy default) and 443 for HTTPS
EXPOSE 80
EXPOSE 443
EXPOSE 443/udp # For HTTP/3

# Entrypoint script to run migrations, clear caches, and start FrankenPHP/Octane
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
# CMD will be set by Octane or a direct FrankenPHP command in entrypoint or docker-compose
# Example if not using Octane directly in CMD: ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
# If using Octane, the entrypoint will likely exec `php artisan octane:start`
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=80"]
