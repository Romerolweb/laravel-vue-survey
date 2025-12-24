#!/bin/bash

#####################################################################
# Test Script for Laravel-Vue Survey Application
# Runs all tests for backend and frontend
#####################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Laravel-Vue Survey - Test Suite${NC}"
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
TEST_TYPE=${1:-all}  # all, backend, frontend, unit, feature

echo "Test Type: $TEST_TYPE"
echo ""

FAILED=0

# Backend tests
if [ "$TEST_TYPE" == "all" ] || [ "$TEST_TYPE" == "backend" ] || [ "$TEST_TYPE" == "unit" ] || [ "$TEST_TYPE" == "feature" ]; then
    print_step "Running backend tests..."
    
    if [ ! -d "vendor" ]; then
        print_step "Installing backend dependencies..."
        composer install --no-interaction
    fi
    
    if [ "$TEST_TYPE" == "unit" ]; then
        print_step "Running PHPUnit Unit tests..."
        if vendor/bin/phpunit --testsuite=Unit --testdox; then
            print_success "Unit tests passed"
        else
            print_error "Unit tests failed"
            FAILED=1
        fi
    elif [ "$TEST_TYPE" == "feature" ]; then
        print_step "Running PHPUnit Feature tests..."
        if vendor/bin/phpunit --testsuite=Feature --testdox; then
            print_success "Feature tests passed"
        else
            print_error "Feature tests failed"
            FAILED=1
        fi
    else
        print_step "Running all PHPUnit tests..."
        if vendor/bin/phpunit --testdox; then
            print_success "Backend tests passed"
        else
            print_error "Backend tests failed"
            FAILED=1
        fi
    fi
    echo ""
fi

# Frontend tests
if [ "$TEST_TYPE" == "all" ] || [ "$TEST_TYPE" == "frontend" ]; then
    print_step "Running frontend tests..."
    cd vue
    
    if [ ! -d "node_modules" ]; then
        print_step "Installing frontend dependencies..."
        npm install
    fi
    
    # Check if test script exists
    if grep -q '"test"' package.json; then
        print_step "Running npm test..."
        if npm run test; then
            print_success "Frontend tests passed"
        else
            print_error "Frontend tests failed"
            FAILED=1
        fi
    else
        print_step "No frontend test script found (skipping)"
    fi
    
    cd ..
    echo ""
fi

# Summary
echo -e "${BLUE}========================================${NC}"
if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}  All tests passed!${NC}"
    echo -e "${GREEN}========================================${NC}"
    exit 0
else
    echo -e "${RED}  Some tests failed!${NC}"
    echo -e "${RED}========================================${NC}"
    exit 1
fi
