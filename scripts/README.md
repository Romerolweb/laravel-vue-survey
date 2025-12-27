# Development & Build Scripts

This directory contains utility scripts for developing, testing, and building the Laravel-Vue Survey application.

## Available Scripts

### 1. Setup Development Environment

```bash
./scripts/setup-dev.sh
```

**Purpose**: Sets up the complete development environment from scratch

**What it does**:
- Checks for required prerequisites (PHP, Composer, Node.js, npm, Docker)
- Offers choice between Docker-based or local development
- Installs all dependencies (backend and frontend)
- Creates .env file if needed
- Generates application key
- Runs database migrations (optional)
- Sets up storage directories and permissions

**When to use**:
- First time setting up the project
- After cloning the repository
- When switching between development modes

**Requirements**:
- PHP 8.2+ (for local development)
- Composer
- Node.js 18+
- npm
- Docker & Docker Compose (optional, for Docker mode)

---

### 2. Start Development Servers

```bash
./scripts/dev.sh
```

**Purpose**: Starts both Laravel backend and Vue frontend development servers

**What it does**:
- Starts Laravel backend on http://localhost:8000
- Starts Vue frontend on http://localhost:3000
- Displays server status and logs location
- Handles graceful shutdown with Ctrl+C

**Output**:
```
========================================
  Laravel-Vue Survey - Dev Server
========================================

✓ Backend started successfully (PID: 12345)
✓ Frontend started successfully (PID: 12346)

========================================
  Development servers are running!
========================================

  Backend:  http://localhost:8000
  Frontend: http://localhost:3000

Logs:
  Backend:  tail -f /tmp/laravel-dev.log
  Frontend: tail -f /tmp/vue-dev.log

Press Ctrl+C to stop servers
```

**Logs**:
- Backend: `/tmp/laravel-dev.log`
- Frontend: `/tmp/vue-dev.log`

**When to use**:
- Daily development work
- Testing changes locally
- Frontend and backend development

**Prerequisites**:
- Development environment already set up
- .env file configured

---

### 3. Run Tests

```bash
./scripts/test.sh [type]
```

**Purpose**: Runs test suites for backend and/or frontend

**Arguments**:
- `all` (default) - Run all tests
- `backend` - Run only backend tests
- `frontend` - Run only frontend tests
- `unit` - Run only unit tests
- `feature` - Run only feature tests

**Examples**:
```bash
# Run all tests
./scripts/test.sh

# Run only backend tests
./scripts/test.sh backend

# Run only unit tests
./scripts/test.sh unit

# Run only feature tests
./scripts/test.sh feature

# Run only frontend tests
./scripts/test.sh frontend
```

**What it does**:
- Installs dependencies if needed
- Runs PHPUnit for Laravel tests
- Runs npm test for Vue tests (if configured)
- Displays test results with color coding
- Returns exit code 0 (success) or 1 (failure)

**When to use**:
- Before committing changes
- After making modifications
- As part of CI/CD pipeline
- To verify everything works

---

### 4. Build for Production

```bash
./scripts/build.sh [build_type] [output_dir]
```

**Purpose**: Creates production-ready build of the application

**Arguments**:
- `build_type` - `production` (default) or `staging`
- `output_dir` - Output directory (default: `./build`)

**Examples**:
```bash
# Production build (default)
./scripts/build.sh

# Staging build
./scripts/build.sh staging

# Custom output directory
./scripts/build.sh production ./dist

# Staging build with custom output
./scripts/build.sh staging ./staging-build
```

**What it does**:
1. **Backend**:
   - Installs production dependencies (no dev packages)
   - Optimizes autoloader
   - Caches config, routes, and views
   - Copies files to output directory

2. **Frontend**:
   - Installs dependencies
   - Runs production build (minified, optimized)
   - Copies dist files to output directory

3. **Additional**:
   - Creates `build-info.json` with metadata
   - Creates `DEPLOY.md` with deployment instructions
   - Creates compressed archive (.tar.gz)

**Output Structure**:
```
build/
├── backend/          # Laravel application
├── frontend/         # Vue.js compiled assets
├── build-info.json   # Build metadata
└── DEPLOY.md         # Deployment instructions
```

**Archive**: `laravel-vue-survey-{type}-{timestamp}.tar.gz`

**When to use**:
- Before deployment to production/staging
- Creating release packages
- Distributing to clients
- Backup before major changes

---

## Quick Reference

| Task | Command |
|------|---------|
| Initial setup | `./scripts/setup-dev.sh` |
| Start development | `./scripts/dev.sh` |
| Run all tests | `./scripts/test.sh` |
| Run unit tests | `./scripts/test.sh unit` |
| Production build | `./scripts/build.sh` |
| Staging build | `./scripts/build.sh staging` |

---

## Workflows

### First Time Setup

```bash
# 1. Clone repository
git clone <repository-url>
cd laravel-vue-survey

# 2. Run setup
./scripts/setup-dev.sh

# 3. Configure .env
nano .env  # or your preferred editor

# 4. Start development
./scripts/dev.sh
```

### Daily Development

```bash
# Start servers
./scripts/dev.sh

# Make changes...

# Run tests
./scripts/test.sh

# Commit changes
git add .
git commit -m "Your message"
```

### Before Deployment

```bash
# Run all tests
./scripts/test.sh

# Create production build
./scripts/build.sh production

# Test the build
# (Upload to staging server and verify)

# Deploy to production
# (Follow instructions in build/DEPLOY.md)
```

---

## Docker Development

If you chose Docker during setup:

### Start Services
```bash
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
```

### View Logs
```bash
docker-compose logs -f
docker-compose logs -f backend
docker-compose logs -f frontend
```

### Run Commands
```bash
# Backend commands
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan cache:clear
docker-compose exec backend composer install

# Frontend commands
docker-compose exec frontend npm install
docker-compose exec frontend npm run build

# Run tests
docker-compose exec backend vendor/bin/phpunit
```

### Rebuild Containers
```bash
docker-compose build
docker-compose up -d
```

---

## Troubleshooting

### "Permission denied" when running scripts

**Solution**:
```bash
chmod +x scripts/*.sh
```

### "command not found" errors

**Cause**: Required tools not installed

**Solution**: Install missing prerequisites:
- PHP: `sudo apt-get install php8.2`
- Composer: https://getcomposer.org/download/
- Node.js: https://nodejs.org/
- Docker: https://docs.docker.com/get-docker/

### Port already in use

**Error**: `Address already in use`

**Solution**:
```bash
# Find process using port 8000
lsof -i :8000

# Kill process
kill -9 <PID>

# Or use different ports
php artisan serve --port=8001
```

### Database connection errors

**Solution**:
1. Check .env database settings
2. Ensure MySQL is running
3. Verify credentials
4. Run migrations: `php artisan migrate`

### Frontend build fails

**Solution**:
```bash
cd vue
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Permission issues with storage

**Solution**:
```bash
chmod -R 775 storage bootstrap/cache
```

---

## Environment Variables

Key variables in `.env`:

```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_vue_survey
DB_USERNAME=root
DB_PASSWORD=

# For Docker development
DB_HOST=db
```

---

## CI/CD Integration

These scripts are designed to work with CI/CD pipelines:

```yaml
# Example GitHub Actions usage
- name: Run tests
  run: ./scripts/test.sh

- name: Build application
  run: ./scripts/build.sh production
```

See [CI_CD.md](../CI_CD.md) for complete CI/CD documentation.

---

## Script Maintenance

### Adding New Scripts

1. Create script in `scripts/` directory
2. Make it executable: `chmod +x scripts/your-script.sh`
3. Add documentation to this README
4. Test thoroughly
5. Commit and push

### Script Guidelines

- Use bash for portability
- Add color-coded output for clarity
- Include error handling (`set -e`)
- Provide helpful error messages
- Document all arguments
- Test on clean environment

---

## Additional Resources

- **[README.md](../README.md)** - Project overview
- **[DEPLOYMENT.md](../DEPLOYMENT.md)** - Deployment guide
- **[CI_CD.md](../CI_CD.md)** - CI/CD pipelines
- **[FEATURES.md](../FEATURES.md)** - New features documentation

---

**Last Updated**: November 2025  
**Maintained By**: Development Team
