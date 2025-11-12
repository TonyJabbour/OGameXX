#!/bin/sh

# Laravel Artisan Health Check Script
# This script checks Laravel application health via artisan commands

set -e

# Configuration
APP_ENV=${APP_ENV:-production}
MAX_EXECUTION_TIME=${HEALTHCHECK_MAX_EXECUTION_TIME:-30}

echo "Checking Laravel application health..."

# Change to app directory
cd /var/www/html

# Check if .env file exists
if [ ! -f .env ]; then
    echo "ERROR: .env file not found"
    exit 1
fi

# Check if APP_KEY is set
app_key=$(grep -E "^APP_KEY=" .env 2>/dev/null | cut -d '=' -f2 | tr -d '[:space:]' | tr -d '\r' || echo "")
if [ -z "$app_key" ]; then
    echo "ERROR: APP_KEY is not set"
    exit 1
fi

# Test database connectivity
echo "Checking database connectivity..."
if ! timeout ${MAX_EXECUTION_TIME} php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; then
    echo "ERROR: Database connection failed"
    exit 1
fi

# Check storage permissions
echo "Checking storage permissions..."
storage_dirs="storage/logs storage/framework/cache storage/framework/sessions storage/framework/views storage/app storage/app/public bootstrap/cache"

for dir in $storage_dirs; do
    if [ ! -d "$dir" ]; then
        echo "WARNING: Storage directory $dir does not exist"
    elif [ ! -w "$dir" ]; then
        echo "ERROR: Storage directory $dir is not writable"
        exit 1
    fi
done

# Check if vendor directory exists and is readable
if [ ! -d "vendor" ]; then
    echo "ERROR: vendor directory not found, run composer install"
    exit 1
fi

if [ ! -r "vendor" ]; then
    echo "ERROR: vendor directory is not readable"
    exit 1
fi

# Test route caching (if in production)
if [ "$APP_ENV" = "production" ]; then
    echo "Testing route cache..."
    if [ -f bootstrap/cache/routes-v7.php ]; then
        # Route cache exists, verify it works
        route_test=$(timeout ${MAX_EXECUTION_TIME} php artisan route:list --path=api 2>/dev/null || echo "")
        if [ -z "$route_test" ]; then
            echo "WARNING: Route cache may be corrupted"
        fi
    fi
fi

# Optional: Check cache status
if [ -d "bootstrap/cache" ]; then
    cache_files=$(find bootstrap/cache -type f 2>/dev/null | wc -l)
    echo "Found $cache_files cache files"
fi

echo "Laravel application health check passed"
exit 0
