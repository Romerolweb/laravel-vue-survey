# Survey Full Stack Application

Built with these technologies for [YouTube Video](https://youtu.be/WLQDpY7lOLg)
<table>
    <tr>
        <td>
            <a href="https://laravel.com"><img src="https://i.imgur.com/pBNT1yy.png" /></a>
        </td>
        <td>
            <a href="https://vuejs.org/"><img src="https://i.imgur.com/BxQe48y.png" /></a>
        </td>
        <td>
            <a href="https://tailwindcss.com/"><img src="https://i.imgur.com/wdYXsgR.png" /></a>
        </td>
        <td>
            <img src="https://i.imgur.com/Kp5kTUp.png" />
        </td>
    </tr>
</table> 

## Features

### Core Survey Platform
- Create and manage surveys with multiple question types
- User authentication and authorization
- Public survey links for data collection
- Dashboard for viewing survey responses

### Environmental Research Features
This application includes specialized features for environmental research projects:

- **GPS Location Collection**: Automatically collect location data (with user consent) when surveys are completed
- **Environmental Footprint Calculation**: Automated calculation of water footprint for wine production surveys
- **Research Documentation**: Comprehensive documentation for research methodology and data analysis

For detailed information about environmental features, see:
- **[ENVIRONMENTAL_FOOTPRINT.md](ENVIRONMENTAL_FOOTPRINT.md)** - Technical documentation
- **[RESEARCH_PROJECT.md](RESEARCH_PROJECT.md)** - Research methodology and project overview


## Requirements

The primary method for running this project is using Docker and Docker Compose.
*   **Docker**: [Install Docker](https://docs.docker.com/get-docker/)
*   **Docker Compose**: [Install Docker Compose](https://docs.docker.com/compose/install/)

For manual local development outside of Docker (not covered by the primary deployment guide), you would need:
*   PHP version **8.2** or above (due to Laravel Octane and FrankenPHP integration).
*   Node.js version **18.0** or above (for Vue.js frontend).
*   Composer for PHP dependencies.
*   NPM for Node.js dependencies.
*   A MySQL database server.

## Demo
https://yoursurveys.xyz (Note: This is a placeholder link from the original project.)


## Quick Start / Deployment

### Development Scripts

Convenient scripts are provided for common development tasks:

```bash
# Setup development environment (first time)
./scripts/setup-dev.sh

# Start development servers (backend + frontend)
./scripts/dev.sh

# Run tests
./scripts/test.sh

# Build for production
./scripts/build.sh
```

See **[scripts/README.md](scripts/README.md)** for detailed script documentation.

### Docker Deployment

For detailed setup and deployment instructions using Docker (recommended), please see:
**[DEPLOYMENT.md](DEPLOYMENT.md)**

This guide covers:
*   Configuring your environment.
*   Building and running the application with Docker Compose.
*   Service details (Backend with FrankenPHP, Frontend, Database).

## Documentation

### New Features
- **[FEATURES.md](FEATURES.md)** - Complete guide to GPS location collection and environmental footprint calculation

### Research & Environmental
- **[ENVIRONMENTAL_FOOTPRINT.md](ENVIRONMENTAL_FOOTPRINT.md)** - Technical documentation for footprint calculator
- **[RESEARCH_PROJECT.md](RESEARCH_PROJECT.md)** - Research methodology and project overview

### Development
- **[scripts/README.md](scripts/README.md)** - Development and build scripts guide
- **[CI_CD.md](CI_CD.md)** - CI/CD pipeline documentation

### Deployment
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Docker deployment guide

## CI/CD

This project includes comprehensive CI/CD pipelines using GitHub Actions:

- **Laravel CI**: Automated testing with PHPUnit, code quality checks (PHP 8.2, 8.3)
- **Vue CI**: Build verification, security audits, multi-version testing (Node 18, 20)
- **Docker CI**: Container builds, integration tests, automated deployment

For detailed information about CI/CD setup and usage, see:
**[CI_CD.md](CI_CD.md)** - Complete CI/CD pipeline documentation


## License

The project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

