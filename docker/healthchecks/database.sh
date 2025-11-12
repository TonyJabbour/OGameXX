#!/bin/sh

# Database Health Check Script
# This script checks database connectivity

set -e

# Configuration
DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-laravel}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}
MAX_EXECUTION_TIME=${HEALTHCHECK_MAX_EXECUTION_TIME:-10}
TIMEOUT=${HEALTHCHECK_TIMEOUT:-5}

echo "Checking database connectivity to ${DB_HOST}:${DB_PORT}..."

# Check if database host is reachable
if ! timeout ${TIMEOUT} nc -z ${DB_HOST} ${DB_PORT} > /dev/null 2>&1; then
    echo "ERROR: Cannot reach database host ${DB_HOST}:${DB_PORT}"
    exit 1
fi

# Try to connect using mysql client if available
if command -v mysql > /dev/null 2>&1; then
    echo "Testing MySQL connection..."
    if timeout ${MAX_EXECUTION_TIME} mysql -h${DB_HOST} -P${DB_PORT} -u${DB_USERNAME} -p${DB_PASSWORD} -e "SELECT 1;" ${DB_DATABASE} > /dev/null 2>&1; then
        echo "Database connection successful"
    else
        echo "ERROR: MySQL connection failed"
        exit 1
    fi
    
    # Check database status
    db_status=$(timeout ${MAX_EXECUTION_TIME} mysql -h${DB_HOST} -P${DB_PORT} -u${DB_USERNAME} -p${DB_PASSWORD} -e "SHOW STATUS LIKE 'Uptime';" ${DB_DATABASE} 2>/dev/null | grep "Uptime" || echo "")
    if [ -n "$db_status" ]; then
        echo "Database is running: $db_status"
    fi
    
    # Check connections
    connections=$(timeout ${MAX_EXECUTION_TIME} mysql -h${DB_HOST} -P${DB_PORT} -u${DB_USERNAME} -p${DB_PASSWORD} -e "SHOW STATUS LIKE 'Threads_connected';" ${DB_DATABASE} 2>/dev/null | grep "Threads_connected" | awk '{print $2}' || echo "0")
    echo "Active connections: $connections"
    
else
    # Fallback: Try to connect using PHP
    echo "Using PHP to test database connection..."
    php_test=$(timeout ${MAX_EXECUTION_TIME} php -r "
        try {
            \$pdo = new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}', [
                PDO::ATTR_TIMEOUT => ${MAX_EXECUTION_TIME},
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            \$stmt = \$pdo->query('SELECT 1');
            echo 'success';
        } catch (Exception \$e) {
            echo 'failed';
            exit(1);
        }
    " 2>/dev/null || echo "failed")
    
    if [ "$php_test" != "success" ]; then
        echo "ERROR: Database connection failed via PHP"
        exit 1
    fi
    
    echo "Database connection successful via PHP"
fi

# Test if we can access Laravel migrations table (if exists)
if [ -d "/var/www/html" ] && [ -f "/var/www/html/artisan" ]; then
    cd /var/www/html
    if timeout ${MAX_EXECUTION_TIME} php artisan tinker --execute="try { DB::table('migrations')->count(); echo 'OK'; } catch (Exception \$e) { echo 'No migrations table'; }" 2>/dev/null | grep -q "OK"; then
        echo "Migrations table accessible"
    fi
fi

echo "Database health check passed"
exit 0
