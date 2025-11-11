#!/bin/bash

#####################################################################
# Development Server Script for Laravel-Vue Survey Application
# Starts both backend and frontend development servers
#####################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Laravel-Vue Survey - Dev Server${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if running in project root
if [ ! -f "composer.json" ] || [ ! -d "vue" ]; then
    echo -e "${RED}✗ Please run this script from the project root directory${NC}"
    exit 1
fi

# Check if .env exists
if [ ! -f ".env" ]; then
    echo -e "${RED}✗ .env file not found. Run ./scripts/setup-dev.sh first${NC}"
    exit 1
fi

# Function to cleanup on exit
cleanup() {
    echo ""
    echo -e "${YELLOW}Shutting down servers...${NC}"
    kill $BACKEND_PID 2>/dev/null || true
    kill $FRONTEND_PID 2>/dev/null || true
    exit 0
}

trap cleanup SIGINT SIGTERM

echo -e "${GREEN}Starting development servers...${NC}"
echo ""

# Start Laravel backend
echo -e "${BLUE}► Starting Laravel backend on http://localhost:8000${NC}"
php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel-dev.log 2>&1 &
BACKEND_PID=$!

# Wait a moment for Laravel to start
sleep 2

# Check if backend started successfully
if ps -p $BACKEND_PID > /dev/null; then
    echo -e "${GREEN}✓ Backend started successfully (PID: $BACKEND_PID)${NC}"
else
    echo -e "${RED}✗ Failed to start backend. Check /tmp/laravel-dev.log${NC}"
    exit 1
fi

# Start Vue frontend
echo -e "${BLUE}► Starting Vue frontend on http://localhost:3000${NC}"
cd vue
npm run dev > /tmp/vue-dev.log 2>&1 &
FRONTEND_PID=$!
cd ..

# Wait a moment for Vue to start
sleep 3

# Check if frontend started successfully
if ps -p $FRONTEND_PID > /dev/null; then
    echo -e "${GREEN}✓ Frontend started successfully (PID: $FRONTEND_PID)${NC}"
else
    echo -e "${RED}✗ Failed to start frontend. Check /tmp/vue-dev.log${NC}"
    kill $BACKEND_PID 2>/dev/null || true
    exit 1
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Development servers are running!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "  Backend:  http://localhost:8000"
echo "  Frontend: http://localhost:3000"
echo ""
echo "Logs:"
echo "  Backend:  tail -f /tmp/laravel-dev.log"
echo "  Frontend: tail -f /tmp/vue-dev.log"
echo ""
echo -e "${YELLOW}Press Ctrl+C to stop servers${NC}"
echo ""

# Wait for processes
wait
