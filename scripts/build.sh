#!/bin/bash

#####################################################################
# Build Script for Laravel-Vue Survey Application
# Builds production-ready assets for both backend and frontend
#####################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Laravel-Vue Survey - Build Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_step() {
    echo -e "${BLUE}► $1${NC}"
}

# Check if running in project root
if [ ! -f "composer.json" ] || [ ! -d "vue" ]; then
    print_error "Please run this script from the project root directory"
    exit 1
fi

# Parse command line arguments
BUILD_TYPE=${1:-production}  # Default to production
OUTPUT_DIR=${2:-./build}

if [ "$BUILD_TYPE" != "production" ] && [ "$BUILD_TYPE" != "staging" ]; then
    print_error "Invalid build type. Use 'production' or 'staging'"
    exit 1
fi

echo "Build Type: $BUILD_TYPE"
echo "Output Directory: $OUTPUT_DIR"
echo ""

# Create output directory
print_step "Creating output directory..."
mkdir -p "$OUTPUT_DIR"
print_success "Output directory created: $OUTPUT_DIR"

# Backend build
print_step "Building backend..."

print_step "Installing production dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
print_success "Backend dependencies installed"

print_step "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Laravel optimization complete"

print_step "Copying backend files to output..."
rsync -av --exclude='node_modules' \
    --exclude='tests' \
    --exclude='storage/logs/*' \
    --exclude='.git' \
    --exclude='.env' \
    --exclude='vue' \
    --exclude="$OUTPUT_DIR" \
    ./ "$OUTPUT_DIR/backend/"
print_success "Backend files copied"

# Frontend build
print_step "Building frontend..."
cd vue

print_step "Installing frontend dependencies..."
npm ci --production=false
print_success "Frontend dependencies installed"

print_step "Building Vue application..."
if [ "$BUILD_TYPE" == "production" ]; then
    npm run build
else
    npm run build -- --mode staging
fi
print_success "Frontend build complete"

print_step "Copying frontend build to output..."
mkdir -p "../$OUTPUT_DIR/frontend"
cp -r dist/* "../$OUTPUT_DIR/frontend/"
print_success "Frontend files copied"

cd ..

# Create deployment info
print_step "Creating build info..."
cat > "$OUTPUT_DIR/build-info.json" <<EOF
{
  "build_type": "$BUILD_TYPE",
  "build_date": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
  "git_commit": "$(git rev-parse HEAD)",
  "git_branch": "$(git rev-parse --abbrev-ref HEAD)",
  "backend_php_version": "$(php -r 'echo PHP_VERSION;')",
  "frontend_node_version": "$(node --version)"
}
EOF
print_success "Build info created"

# Create deployment instructions
print_step "Creating deployment instructions..."
cat > "$OUTPUT_DIR/DEPLOY.md" <<'EOF'
# Deployment Instructions

## Backend Deployment

1. Upload the `backend` directory to your server
2. Copy `.env.example` to `.env` and configure:
   - Database credentials
   - APP_KEY (generate with: `php artisan key:generate`)
   - APP_URL
3. Set proper permissions:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
4. Run migrations:
   ```bash
   php artisan migrate --force
   ```
5. Configure your web server to point to `public` directory

## Frontend Deployment

1. Upload the `frontend` directory contents to your web server
2. Configure your web server to serve the files
3. For SPA routing, redirect all requests to `index.html`

## Nginx Configuration Example

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/backend/public;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

server {
    listen 80;
    server_name app.yourdomain.com;
    root /path/to/frontend;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

## Post-Deployment

1. Clear caches if needed:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

2. Verify the application is working correctly

3. Monitor logs in `storage/logs`
EOF
print_success "Deployment instructions created"

# Create archive
print_step "Creating archive..."
ARCHIVE_NAME="laravel-vue-survey-${BUILD_TYPE}-$(date +%Y%m%d-%H%M%S).tar.gz"
tar -czf "$ARCHIVE_NAME" -C "$OUTPUT_DIR" .
print_success "Archive created: $ARCHIVE_NAME"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Build completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Build output: $OUTPUT_DIR"
echo "Archive: $ARCHIVE_NAME"
echo ""
echo "Contents:"
echo "  - backend/        Laravel application"
echo "  - frontend/       Vue.js build"
echo "  - build-info.json Build metadata"
echo "  - DEPLOY.md       Deployment instructions"
echo ""
print_success "Ready for deployment!"
