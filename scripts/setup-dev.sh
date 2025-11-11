#!/bin/bash

#####################################################################
# Development Setup Script for Laravel-Vue Survey Application
# This script sets up the development environment
#####################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Laravel-Vue Survey - Development Setup${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

print_step() {
    echo -e "${BLUE}► $1${NC}"
}

# Check if running in project root
if [ ! -f "composer.json" ] || [ ! -d "vue" ]; then
    print_error "Please run this script from the project root directory"
    exit 1
fi

# Check prerequisites
print_step "Checking prerequisites..."

# Check Docker
if command -v docker &> /dev/null; then
    print_success "Docker is installed"
    DOCKER_AVAILABLE=true
else
    print_info "Docker not found (optional for Docker-based development)"
    DOCKER_AVAILABLE=false
fi

# Check PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r 'echo PHP_VERSION;')
    print_success "PHP $PHP_VERSION is installed"
else
    print_error "PHP is not installed (required for local development)"
fi

# Check Composer
if command -v composer &> /dev/null; then
    print_success "Composer is installed"
else
    print_error "Composer is not installed (required)"
fi

# Check Node.js
if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version)
    print_success "Node.js $NODE_VERSION is installed"
else
    print_error "Node.js is not installed (required)"
fi

# Check npm
if command -v npm &> /dev/null; then
    NPM_VERSION=$(npm --version)
    print_success "npm $NPM_VERSION is installed"
else
    print_error "npm is not installed (required)"
fi

echo ""

# Ask user for development mode
echo "Choose development mode:"
echo "1) Docker (recommended)"
echo "2) Local development"
read -p "Enter choice [1-2]: " DEV_MODE

if [ "$DEV_MODE" == "1" ]; then
    # Docker-based development
    print_step "Setting up Docker-based development environment..."
    
    if [ "$DOCKER_AVAILABLE" != true ]; then
        print_error "Docker is required for this mode. Please install Docker first."
        exit 1
    fi
    
    # Create .env if it doesn't exist
    if [ ! -f ".env" ]; then
        print_step "Creating .env file..."
        cp .env.example .env
        print_success ".env file created"
    else
        print_info ".env file already exists"
    fi
    
    print_step "Building Docker containers..."
    docker-compose build
    
    print_step "Starting Docker containers..."
    docker-compose up -d
    
    print_step "Waiting for services to start..."
    sleep 10
    
    print_step "Installing backend dependencies..."
    docker-compose exec backend composer install
    
    print_step "Generating application key..."
    docker-compose exec backend php artisan key:generate
    
    print_step "Running database migrations..."
    docker-compose exec backend php artisan migrate
    
    print_step "Installing frontend dependencies..."
    docker-compose exec frontend npm install
    
    echo ""
    print_success "Docker development environment is ready!"
    echo ""
    echo "Services running at:"
    echo "  Backend:  http://localhost:8000"
    echo "  Frontend: http://localhost:3000"
    echo ""
    echo "Useful commands:"
    echo "  View logs:        docker-compose logs -f"
    echo "  Stop services:    docker-compose down"
    echo "  Restart services: docker-compose restart"
    echo ""

else
    # Local development
    print_step "Setting up local development environment..."
    
    # Create .env if it doesn't exist
    if [ ! -f ".env" ]; then
        print_step "Creating .env file..."
        cp .env.example .env
        print_success ".env file created"
        print_info "Please configure your database settings in .env"
    else
        print_info ".env file already exists"
    fi
    
    # Backend setup
    print_step "Installing backend dependencies..."
    composer install --no-interaction
    print_success "Backend dependencies installed"
    
    print_step "Generating application key..."
    php artisan key:generate
    print_success "Application key generated"
    
    print_step "Creating storage directories..."
    mkdir -p storage/framework/{sessions,views,cache}
    mkdir -p storage/logs
    chmod -R 775 storage bootstrap/cache
    print_success "Storage directories created"
    
    # Ask about database setup
    read -p "Do you want to run database migrations now? (y/n): " RUN_MIGRATIONS
    if [ "$RUN_MIGRATIONS" == "y" ] || [ "$RUN_MIGRATIONS" == "Y" ]; then
        print_step "Running database migrations..."
        php artisan migrate
        print_success "Migrations completed"
    else
        print_info "Skipping migrations. Run 'php artisan migrate' manually when ready."
    fi
    
    # Frontend setup
    print_step "Setting up frontend..."
    cd vue
    
    print_step "Installing frontend dependencies..."
    npm install
    print_success "Frontend dependencies installed"
    
    cd ..
    
    echo ""
    print_success "Local development environment is ready!"
    echo ""
    echo "To start development:"
    echo "  Backend:  php artisan serve"
    echo "  Frontend: cd vue && npm run dev"
    echo ""
    echo "Or use the convenience script:"
    echo "  ./scripts/dev.sh"
    echo ""
fi

print_success "Setup complete!"
