# Docker Health Checks

This directory contains custom health check scripts for different container services.

## Available Scripts

- `php-fpm.sh` - Checks PHP-FPM service health
- `artisan.sh` - Checks Laravel application health via artisan commands
- `database.sh` - Checks database connectivity
- `queue.sh` - Checks queue worker health
- `storage.sh` - Checks storage permissions and accessibility

## Usage

These scripts can be used in Docker health checks:

```dockerfile
HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
    CMD docker/healthchecks/php-fpm.sh
```

## Customization

Each script can be customized by setting environment variables or modifying the script logic based on your specific requirements.
