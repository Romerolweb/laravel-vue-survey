# Use an official PHP image with FPM and version 8.0
FROM php:8.0-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
# Alpine Linux uses 'apk' package manager
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libintl \
    icu-dev \ # For intl extension
    libgomp; # May be needed for some composer packages or their dependencies

# Install PHP extensions
# Note: Alpine might require different names or methods for some extensions
# For example, pdo_mysql is often included or handled via mysqlnd
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    intl \
    opcache \
    pdo pdo_mysql \
    zip \
    bcmath \
    exif # Often needed by Laravel for file uploads/validation
# mbstring and tokenizer are often included in official images or easily installed
RUN docker-php-ext-install -j$(nproc) mbstring tokenizer xml

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . .

# Copy Nginx configuration
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
# Copy Supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Set permissions for storage and bootstrap/cache (if necessary, often handled by entrypoint or post-build)
# RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
# RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Environment variables for Laravel (can be overridden in docker-compose)
ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=mysql \
    DB_HOST=db \
    DB_PORT=3306 \
    DB_DATABASE=laravel_vue_survey \
    DB_USERNAME=root \
    DB_PASSWORD=123456

# Expose port 80 for Nginx
EXPOSE 80

# Entrypoint script to run migrations, clear caches, and start services
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
