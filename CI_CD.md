# CI/CD Pipeline Documentation

## Overview

This project includes comprehensive CI/CD pipelines using GitHub Actions for automated testing, building, and deployment of both the Laravel backend and Vue.js frontend.

## Workflows

### 1. Laravel CI (`laravel.yml`)

**Purpose**: Automated testing and code quality checks for the Laravel backend.

**Triggers**:
- Push to `main` branch or any `copilot/**` branch
- Pull requests to `main` branch

**Jobs**:

#### `laravel-tests`
- **Matrix**: PHP 8.2 and 8.3
- **Database**: MySQL 8.0 (test database)
- **Steps**:
  1. Checkout code
  2. Setup PHP with required extensions
  3. Cache Composer dependencies
  4. Install dependencies
  5. Setup Laravel environment (.env, app key)
  6. Set directory permissions
  7. Run database migrations
  8. Execute PHPUnit tests (Unit and Feature)

#### `code-quality`
- **PHP Version**: 8.2
- **Steps**:
  1. Checkout code
  2. Setup PHP
  3. Install dependencies
  4. Run PHP CodeSniffer (PSR-12 standard) - optional
  5. Run PHPStan static analysis - optional

**Environment Variables**:
```yaml
DB_CONNECTION: mysql
DB_HOST: 127.0.0.1
DB_PORT: 3306
DB_DATABASE: laravel_test
DB_USERNAME: root
DB_PASSWORD: password
```

### 2. Vue CI (`vue.yml`)

**Purpose**: Build and test the Vue.js frontend application.

**Triggers**:
- Push to `main` branch or any `copilot/**` branch
- Pull requests to `main` branch

**Jobs**:

#### `vue-build-and-test`
- **Matrix**: Node.js 18.x and 20.x
- **Working Directory**: `./vue`
- **Steps**:
  1. Checkout code
  2. Setup Node.js with npm cache
  3. Install dependencies with `npm ci`
  4. Run linting (if configured)
  5. Run tests (if configured)
  6. Build application with `npm run build`
  7. Verify build output (dist directory)
  8. Upload build artifacts (retained for 7 days)

#### `vue-code-quality`
- **Node.js Version**: 20.x
- **Steps**:
  1. Checkout code
  2. Setup Node.js
  3. Install dependencies
  4. Run security audit (`npm audit`)
  5. Run type checking (if configured)

### 3. Docker Build and Deploy (`docker.yml`)

**Purpose**: Build Docker images and test containerized deployment.

**Triggers**:
- Push to `main` branch
- Pull requests to `main` branch
- Manual workflow dispatch

**Jobs**:

#### `build-backend`
- Builds backend Docker image using `backend.Dockerfile`
- Uses Docker BuildKit with layer caching
- Pushes to GitHub Container Registry (GHCR) on main branch
- Tags: branch name, PR number, semantic version, commit SHA

#### `build-frontend`
- Builds frontend Docker image using `frontend.Dockerfile`
- Uses Docker BuildKit with layer caching
- Pushes to GitHub Container Registry (GHCR) on main branch
- Tags: branch name, PR number, semantic version, commit SHA

#### `integration-test`
- Runs only on pull requests
- Starts services using docker-compose
- Tests backend and frontend endpoints
- Cleans up containers after tests

**Container Registry**:
- Registry: GitHub Container Registry (ghcr.io)
- Images:
  - `ghcr.io/[owner]/[repo]-backend`
  - `ghcr.io/[owner]/[repo]-frontend`

## Setup Requirements

### Repository Secrets

No additional secrets are required for basic CI/CD. The workflows use `GITHUB_TOKEN` which is automatically provided.

For custom deployments, you may need to add:
- `DEPLOY_SSH_KEY` - SSH key for deployment server
- `DEPLOY_HOST` - Deployment server hostname
- `DEPLOY_USER` - Deployment server username
- Custom registry credentials (if not using GHCR)

### Branch Protection Rules

Recommended branch protection for `main`:
1. Require pull request reviews before merging
2. Require status checks to pass:
   - `laravel-tests`
   - `vue-build-and-test`
   - `code-quality`
   - `vue-code-quality`
3. Require branches to be up to date before merging
4. Include administrators in restrictions

## Usage

### Running Workflows Locally

#### Laravel Tests
```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Run tests
vendor/bin/phpunit
```

#### Vue Build
```bash
cd vue

# Install dependencies
npm ci

# Build application
npm run build

# Check build output
ls -la dist/
```

#### Docker Build
```bash
# Build backend
docker build -f backend.Dockerfile -t survey-backend .

# Build frontend
docker build -f frontend.Dockerfile -t survey-frontend .

# Run with docker-compose
docker-compose up -d
```

### Viewing Workflow Results

1. Go to the repository on GitHub
2. Click on "Actions" tab
3. Select a workflow from the left sidebar
4. Click on a specific run to see details
5. View logs for each job and step

### Build Artifacts

Vue build artifacts are automatically uploaded and can be:
- Downloaded from workflow run page
- Retained for 7 days
- Used for deployment verification

## CI/CD Best Practices

### What Gets Tested

**Laravel**:
- ✅ Unit tests
- ✅ Feature tests
- ✅ Database migrations
- ✅ Code style (PSR-12)
- ✅ Static analysis
- ✅ Multiple PHP versions

**Vue**:
- ✅ Build compilation
- ✅ Multiple Node.js versions
- ✅ Security vulnerabilities
- ✅ Linting (if configured)
- ✅ Type checking (if configured)

**Docker**:
- ✅ Backend image build
- ✅ Frontend image build
- ✅ Layer caching
- ✅ Integration tests
- ✅ Container registry push

### Performance Optimizations

1. **Dependency Caching**:
   - Composer cache for Laravel
   - npm cache for Vue
   - Docker layer cache for images

2. **Parallel Execution**:
   - Multiple PHP versions tested simultaneously
   - Multiple Node.js versions tested simultaneously
   - Independent jobs run in parallel

3. **Conditional Execution**:
   - Optional quality checks don't fail builds
   - Integration tests only on PRs
   - Image pushes only on main branch

### Failure Handling

- **Soft Failures**: Optional checks (lint, type-check) use `continue-on-error: true`
- **Required Checks**: Tests and builds must pass
- **Debugging**: Full logs available for each step
- **Notifications**: GitHub automatically notifies on failures

## Extending the Pipelines

### Adding More Tests

**Laravel**:
```yaml
- name: Run additional tests
  run: vendor/bin/phpunit --testsuite=Integration
```

**Vue**:
```yaml
- name: Run E2E tests
  run: npm run test:e2e
```

### Adding Code Coverage

**Laravel**:
```yaml
- name: Generate coverage report
  run: vendor/bin/phpunit --coverage-clover coverage.xml

- name: Upload coverage to Codecov
  uses: codecov/codecov-action@v3
  with:
    files: ./coverage.xml
```

### Adding Deployment

```yaml
deploy:
  runs-on: ubuntu-latest
  needs: [laravel-tests, vue-build-and-test]
  if: github.ref == 'refs/heads/main' && github.event_name == 'push'
  
  steps:
  - name: Deploy to production
    run: |
      # Add deployment commands here
      echo "Deploying to production..."
```

### Adding Slack Notifications

```yaml
- name: Notify Slack
  if: failure()
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

## Troubleshooting

### Common Issues

1. **Composer Installation Fails**
   - Check composer.lock is committed
   - Verify PHP version compatibility
   - Check for memory limits

2. **npm ci Fails**
   - Ensure package-lock.json is committed
   - Check Node.js version compatibility
   - Clear npm cache if needed

3. **Database Connection Issues**
   - Verify MySQL service is healthy
   - Check environment variables
   - Wait for service initialization

4. **Docker Build Fails**
   - Check Dockerfile syntax
   - Verify base images are accessible
   - Check for sufficient disk space

### Getting Help

- Check workflow logs for specific errors
- Review the Actions tab in GitHub
- Verify environment variables and secrets
- Test locally using the same commands

## Monitoring

### Key Metrics to Track

- Build success rate
- Average build time
- Test coverage percentage
- Failed test patterns
- Dependency vulnerabilities

### GitHub Actions Usage

- View usage in repository Settings → Actions
- Monitor billing (if applicable)
- Optimize workflow run time
- Use caching effectively

## Security Considerations

1. **Secrets Management**:
   - Never commit secrets to repository
   - Use GitHub Secrets for sensitive data
   - Rotate credentials regularly

2. **Dependency Scanning**:
   - `npm audit` for Node.js dependencies
   - Consider adding Dependabot
   - Review security advisories

3. **Container Security**:
   - Scan images for vulnerabilities
   - Use minimal base images
   - Keep dependencies updated

4. **Access Control**:
   - Limit workflow write permissions
   - Use GITHUB_TOKEN scope appropriately
   - Review third-party actions

## Maintenance

### Regular Tasks

- Update action versions (e.g., `actions/checkout@v4`)
- Update PHP/Node.js versions in matrix
- Review and update dependencies
- Monitor workflow run times
- Clean up old artifacts

### Version Updates

When updating versions:
1. Test in a feature branch first
2. Update one version at a time
3. Monitor for compatibility issues
4. Update documentation

## Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Vite Build Options](https://vitejs.dev/guide/build.html)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)

---

**Last Updated**: November 2025  
**Maintained By**: Development Team
