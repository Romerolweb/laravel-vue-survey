# Deployment and Setup Guide (Docker with FrankenPHP)

## 1. Project Overview

This is a full-stack survey application built with:
*   **Backend**: Laravel (PHP)
*   **Frontend**: Vue.js (served by Nginx)
*   **Database**: MySQL
*   **Application Server (Backend)**: FrankenPHP with Laravel Octane

The application allows users to create surveys, share them, and view responses. This guide focuses on setting up the development/production environment using Docker and Docker Compose.

## 2. Prerequisites

*   Docker ([Install Docker](https://docs.docker.com/get-docker/))
*   Docker Compose ([Install Docker Compose](https://docs.docker.com/compose/install/))
*   A `.env` file for Laravel (see section 5).

## 3. Dockerized Setup with FrankenPHP

The `docker-compose.yml` file orchestrates the necessary services: frontend, backend (with FrankenPHP), and a MySQL database.

### 3.1. Environment Configuration

Before starting, you may need to configure environment variables.

**For Docker Compose (`.env` file in project root):**

Create a `.env` file in the project root (same directory as `docker-compose.yml`) to customize ports, database credentials, etc. Example:

```env
# Host ports
BACKEND_PORT=8000
FRONTEND_PORT=3000
DB_FORWARD_PORT=33060 # Optional: if you need to access DB from host

# Database Credentials (must match Laravel's .env)
DB_DATABASE=laravel_vue_survey
DB_USERNAME=surveyuser
DB_PASSWORD=surveypass
MYSQL_ROOT_PASSWORD=supersecretrootpassword

# Laravel Application Key (generate if not set, see Laravel .env section)
# APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=

# Frontend API URL (used at build time for the frontend container)
VITE_API_BASE_URL=http://localhost:8000/api # Adjust if BACKEND_PORT is different
```

**For Laravel (`.env` file in Laravel root - i.e., project root):**

Ensure your main Laravel `.env` file (this will be created from `.env.example` by the entrypoint script if it doesn't exist) is configured to connect to the Dockerized database and reflects other settings. Key variables for Docker:

```env
APP_NAME=LaravelSurvey
APP_ENV=local # Or production when deploying
APP_KEY= # Generate using `php artisan key:generate` or copy from docker-compose .env
APP_DEBUG=true # Or false for production
APP_URL=http://localhost:${BACKEND_PORT:-8000} # How Laravel sees its own URL

LOG_CHANNEL=stderr # Good for Docker to output logs to console

DB_CONNECTION=mysql
DB_HOST=db             # Critical: Must be the service name from docker-compose.yml
DB_PORT=3306
DB_DATABASE=${DB_DATABASE} # e.g., laravel_vue_survey (from docker-compose .env)
DB_USERNAME=${DB_USERNAME} # e.g., surveyuser (from docker-compose .env)
DB_PASSWORD=${DB_PASSWORD} # e.g., surveypass (from docker-compose .env)

# Octane specific (usually managed by Octane, but good to be aware)
OCTANE_SERVER=frankenphp
OCTANE_HOST=0.0.0.0
OCTANE_PORT=80 # Internal port FrankenPHP listens on inside the container

# Frontend URL (if needed by Laravel for generating links, etc.)
# FRONTEND_URL=http://localhost:${FRONTEND_PORT:-3000}
```
*Note: The backend entrypoint script will run `php artisan key:generate` if `APP_KEY` is not found in the environment.*

### 3.2. Building and Running Containers

1.  **Build the Docker images:**
    Open a terminal in the project root directory and run:
    ```bash
    docker-compose build
    ```
    This command builds the `frontend` and `backend` images based on their respective Dockerfiles. The `VITE_API_BASE_URL` argument is passed during the frontend build.

2.  **Start the services:**
    ```bash
    docker-compose up -d
    ```
    This starts all services defined in `docker-compose.yml` in detached mode.
    *   **Backend (Laravel with FrankenPHP)**: Accessible on `http://localhost:<BACKEND_PORT>` (default: `http://localhost:8000`).
    *   **Frontend (Vue.js)**: Accessible on `http://localhost:<FRONTEND_PORT>` (default: `http://localhost:3000`).
    *   **Database (MySQL)**: Port `DB_FORWARD_PORT` (default: 33060) on the host is mapped to the MySQL container's port 3306 if you need to connect with an external SQL client.

3.  **Initial Setup (Migrations & Seeding):**
    The backend container's entrypoint script (`docker/entrypoint.sh`) automatically:
    *   Installs composer dependencies (if `vendor` folder is missing).
    *   Generates an application key if needed.
    *   Runs database migrations (`php artisan migrate --force`).
    *   Caches configuration, routes, and views for optimization.
    The database seeders (including the "Calculadora Huella Hidrica" survey) are run as part of `migrate:fresh --seed` if you use that, or you can run them manually:
    ```bash
    docker-compose exec backend php artisan db:seed
    ```

### 3.3. Stopping the Services
    ```bash
    docker-compose down
    ```
    To remove volumes (like database data), use `docker-compose down -v`.

### 3.4. Viewing Logs
    ```bash
    docker-compose logs -f # View logs for all services
    docker-compose logs -f backend # View logs for a specific service
    ```

## 4. Service Details

*   **`backend` (Laravel API with FrankenPHP):**
    *   Uses an official `dunglas/frankenphp` image with PHP 8.2.
    *   Leverages Laravel Octane with FrankenPHP as the server for high performance.
    *   Serves the Laravel API.
    *   Code is mounted from your local directory for development (`./:/app`).
    *   Listens on port 80 internally, mapped to `BACKEND_PORT` on the host.

*   **`frontend` (Vue.js SPA):**
    *   Multi-stage Docker build:
        1.  Builds the Vue.js static assets using Node.js 18.
        2.  Serves these static assets using a lightweight Nginx server.
    *   `VITE_API_BASE_URL` is configured at build time to point to the backend API.
    *   Listens on port 80 internally, mapped to `FRONTEND_PORT` on the host.

*   **`db` (MySQL Database):**
    *   Uses the official `mysql:8.0` image.
    *   Database name, user, and password are configured via environment variables in `docker-compose.yml` (or your root `.env` file).
    *   Data is persisted in a Docker named volume (`survey_db_data`) to survive container restarts.

## 5. FrankenPHP Specifics

*   **What is FrankenPHP?** FrankenPHP is a modern PHP application server written in Go. It embeds PHP directly into a Go binary, often using Caddy as the underlying web server.
*   **Benefits in this project:**
    *   **Simpler Backend Container**: Replaces the traditional Nginx + PHP-FPM + Supervisor setup with a single FrankenPHP process managed by Laravel Octane.
    *   **Performance**: Octane with FrankenPHP can provide significant performance improvements over traditional setups due to stateful application booting and efficient request handling.
*   **Configuration**:
    *   Laravel Octane is configured via `php artisan octane:install --server=frankenphp`.
    *   The backend Docker container starts Octane with `php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=80`.
    *   FrankenPHP specific settings can often be managed via environment variables or a `Caddyfile` if more advanced Caddy features are needed (though Octane handles most common cases).

## 6. Technology Versions (as per Docker setup)

*   **PHP**: 8.2 (via `dunglas/frankenphp:1-php8.2-bookworm`)
*   **Node.js**: 18 (for building frontend, via `node:18-alpine`)
*   **MySQL**: 8.0 (via `mysql:8.0` image)
*   **Nginx**: Stable (for serving frontend, via `nginx:stable-alpine`)

Remember to consult the official documentation for [Laravel](https://laravel.com/docs), [Vue.js](https://vuejs.org/), [FrankenPHP](https://frankenphp.dev/), and [Docker](https://docs.docker.com/) for more in-depth information.
