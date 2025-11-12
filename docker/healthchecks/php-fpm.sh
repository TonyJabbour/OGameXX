#!/bin/sh

# PHP-FPM Health Check Script
# This script checks if PHP-FPM is running and responding

set -e

# Configuration
PHP_FPM_PORT=${PHP_FPM_PORT:-9000}
TIMEOUT=${HEALTHCHECK_TIMEOUT:-5}

echo "Checking PHP-FPM health on port ${PHP_FPM_PORT}..."

# Check if PHP-FPM process is running
if ! pgrep -f "php-fpm.*master" > /dev/null; then
    echo "ERROR: PHP-FPM master process is not running"
    exit 1
fi

# Check if PHP-FPM is listening on the expected port
if command -v netstat > /dev/null 2>&1; then
    if ! netstat -tuln | grep -q ":${PHP_FPM_PORT}"; then
        echo "ERROR: PHP-FPM is not listening on port ${PHP_FPM_PORT}"
        exit 1
    fi
elif command -v ss > /dev/null 2>&1; then
    if ! ss -tuln | grep -q ":${PHP_FPM_PORT}"; then
        echo "ERROR: PHP-FPM is not listening on port ${PHP_FPM_PORT}"
        exit 1
    fi
fi

# Test PHP-FPM response with a simple PHP script
php_response=$(echo '<?php echo "OK"; ?>' | timeout ${TIMEOUT} php 2>/dev/null || echo "")

if [ "$php_response" != "OK" ]; then
    echo "ERROR: PHP is not responding correctly"
    exit 1
fi

# Check PHP-FPM pool status if available
if [ -f /var/run/php-fpm/php-fpm.pid ]; then
    # Try to get status if status_page is enabled
    if [ -f /usr/local/var/log/php-fpm.sock ]; then
        echo "PHP-FPM process is running and responding"
    fi
fi

echo "PHP-FPM health check passed"
exit 0
