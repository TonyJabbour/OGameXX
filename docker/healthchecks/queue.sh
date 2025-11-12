#!/bin/sh

# Queue Worker Health Check Script
# This script checks queue worker health

set -e

# Configuration
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}
MAX_EXECUTION_TIME=${HEALTHCHECK_MAX_EXECUTION_TIME:-15}
TIMEOUT=${HEALTHCHECK_TIMEOUT:-5}

echo "Checking queue worker health..."

cd /var/www/html

# Check if artisan command exists
if [ ! -f artisan ]; then
    echo "ERROR: Laravel artisan command not found"
    exit 1
fi

# Check if .env file exists and has correct queue configuration
if [ ! -f .env ]; then
    echo "ERROR: .env file not found"
    exit 1
fi

# Verify queue configuration
env_queue=$(grep -E "^QUEUE_CONNECTION=" .env 2>/dev/null | cut -d '=' -f2 | tr -d '[:space:]' | tr -d '\r' || echo "")
if [ -n "$env_queue" ]; then
    echo "Queue connection: $env_queue"
fi

# Test queue connection
echo "Testing queue connection..."
queue_test=$(timeout ${MAX_EXECUTION_TIME} php artisan queue:connection 2>/dev/null || echo "failed")
if [ "$queue_test" = "failed" ] || [ -z "$queue_test" ]; then
    echo "ERROR: Queue connection test failed"
    exit 1
fi

echo "Queue connection: $queue_test"

# Check for failed jobs (if using database queue)
if [ "$QUEUE_CONNECTION" = "database" ]; then
    echo "Checking for failed jobs..."
    failed_jobs=$(timeout ${MAX_EXECUTION_TIME} php artisan queue:failed 2>/dev/null | grep -c "No failed jobs" || echo "0")
    if [ "$failed_jobs" = "0" ]; then
        failed_count=$(timeout ${MAX_EXECUTION_TIME} php artisan queue:failed 2>/dev/null | grep -E "^\s*[0-9]+" | wc -l || echo "0")
        if [ "$failed_count" -gt 0 ]; then
            echo "WARNING: Found $failed_count failed jobs"
        else
            echo "No failed jobs found"
        fi
    fi
fi

# Check queue size (if possible)
echo "Checking queue size..."
queue_size=$(timeout ${MAX_EXECUTION_TIME} php artisan queue:size default 2>/dev/null || echo "unknown")
if [ "$queue_size" != "unknown" ]; then
    echo "Queue size: $queue_size jobs"
fi

# Check for running queue workers
echo "Checking for running queue workers..."
worker_count=$(pgrep -f "queue:work" 2>/dev/null | wc -l || echo "0")
echo "Found $worker_count queue worker processes"

if [ $worker_count -eq 0 ]; then
    echo "WARNING: No queue workers found running"
    # This is not necessarily an error - workers might be managed externally
fi

# Test queue by attempting to push a test job (optional, commented out)
# Uncomment the following section if you want to test queue functionality
# echo "Testing queue functionality..."
# test_job=$(timeout ${MAX_EXECUTION_TIME} php artisan queue:work --once --quiet 2>/dev/null || echo "")
# if [ -z "$test_job" ]; then
#     echo "Queue test job executed"
# fi

# Check Redis connection if using Redis queue
if [ "$QUEUE_CONNECTION" = "redis" ]; then
    if command -v redis-cli > /dev/null 2>&1; then
        echo "Testing Redis connection for queue..."
        redis_host=${REDIS_HOST:-redis}
        redis_port=${REDIS_PORT:-6379}
        if timeout ${TIMEOUT} redis-cli -h ${redis_host} -p ${redis_port} ping > /dev/null 2>&1; then
            echo "Redis connection successful"
        else
            echo "ERROR: Redis connection failed"
            exit 1
        fi
    fi
fi

echo "Queue worker health check passed"
exit 0
