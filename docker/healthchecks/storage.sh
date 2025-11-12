#!/bin/sh

# Storage Health Check Script
# This script checks storage permissions and accessibility

set -e

# Configuration
STORAGE_PATH=${STORAGE_PATH:-/var/www/html/storage}
WEB_USER=${WEB_USER:-www-data}
WEB_GROUP=${WEB_GROUP:-www-data}
MAX_EXECUTION_TIME=${HEALTHCHECK_MAX_EXECUTION_TIME:-10}

echo "Checking storage health..."

# Change to app directory
cd /var/www/html

# Required storage directories and their expected permissions
storage_dirs="
storage/app
storage/app/public
storage/framework/cache
storage/framework/cache/data
storage/framework/sessions
storage/framework/views
storage/logs
bootstrap/cache
"

echo "Checking storage directory permissions..."

# Check each required directory
for dir in $storage_dirs; do
    if [ ! -d "$dir" ]; then
        echo "WARNING: Storage directory $dir does not exist"
        # Create directory if it doesn't exist (optional)
        # mkdir -p "$dir"
        # echo "Created directory: $dir"
    elif [ ! -w "$dir" ]; then
        echo "ERROR: Storage directory $dir is not writable by current user"
        
        # Try to fix permissions
        echo "Attempting to fix permissions for $dir..."
        if command -v chown > /dev/null 2>&1; then
            chown -R ${WEB_USER}:${WEB_GROUP} "$dir" 2>/dev/null || echo "Failed to change ownership"
            chmod -R 775 "$dir" 2>/dev/null || echo "Failed to change permissions"
            
            # Verify fix
            if [ -w "$dir" ]; then
                echo "Fixed permissions for $dir"
            else
                echo "ERROR: Could not fix permissions for $dir"
                exit 1
            fi
        else
            echo "ERROR: chown command not available"
            exit 1
        fi
    else
        echo "✓ $dir is writable"
    fi
done

# Test file operations in storage
echo "Testing file operations in storage..."

test_file="$STORAGE_PATH/test_health_check_$(date +%s).tmp"
test_content="Health check test $(date)"

# Test write operation
if echo "$test_content" > "$test_file" 2>/dev/null; then
    echo "✓ Write test successful"
else
    echo "ERROR: Cannot write to storage"
    exit 1
fi

# Test read operation
if [ -f "$test_file" ] && [ "$(cat "$test_file")" = "$test_content" ]; then
    echo "✓ Read test successful"
else
    echo "ERROR: Cannot read from storage"
    rm -f "$test_file"
    exit 1
fi

# Test delete operation
if rm -f "$test_file" 2>/dev/null && [ ! -f "$test_file" ]; then
    echo "✓ Delete test successful"
else
    echo "ERROR: Cannot delete from storage"
    exit 1
fi

# Check disk space
echo "Checking disk space..."
if command -v df > /dev/null 2>&1; then
    disk_usage=$(df -h . | awk 'NR==2 {print $5}' | sed 's/%//')
    if [ "$disk_usage" -gt 90 ]; then
        echo "WARNING: Disk usage is at ${disk_usage}%"
    elif [ "$disk_usage" -gt 95 ]; then
        echo "ERROR: Disk usage is critically high at ${disk_usage}%"
        exit 1
    else
        echo "✓ Disk usage: ${disk_usage}%"
    fi
fi

# Check for large log files
echo "Checking log files..."
if [ -d "storage/logs" ]; then
    log_size=$(du -sh storage/logs 2>/dev/null | cut -f1)
    echo "Log directory size: $log_size"
    
    # Check if there's a recent log entry
    if [ -f "storage/logs/laravel.log" ]; then
        if [ -r "storage/logs/laravel.log" ]; then
            last_log=$(tail -1 storage/logs/laravel.log 2>/dev/null || echo "")
            if [ -n "$last_log" ]; then
                echo "✓ Log file is accessible and writable"
            fi
        else
            echo "WARNING: Log file exists but is not readable"
        fi
    fi
fi

# Check public storage permissions
echo "Checking public storage permissions..."
if [ -d "storage/app/public" ]; then
    if [ ! -w "storage/app/public" ]; then
        echo "WARNING: storage/app/public is not writable"
    else
        # Test creating a file in public storage
        public_test="storage/app/public/health_check_$(date +%s).txt"
        if echo "test" > "$public_test" 2>/dev/null; then
            echo "✓ Public storage is writable"
            rm -f "$public_test"
        else
            echo "ERROR: Cannot write to public storage"
            exit 1
        fi
    fi
fi

# Check session storage
echo "Checking session storage..."
if [ -d "storage/framework/sessions" ]; then
    session_count=$(ls -1 storage/framework/sessions 2>/dev/null | wc -l)
    echo "Found $session_count session files"
    
    # Check if sessions directory is writable
    if touch "storage/framework/sessions/.test_writable" 2>/dev/null; then
        rm -f "storage/framework/sessions/.test_writable"
        echo "✓ Session storage is writable"
    else
        echo "ERROR: Session storage is not writable"
        exit 1
    fi
fi

# Check cache storage
echo "Checking cache storage..."
if [ -d "storage/framework/cache" ]; then
    if touch "storage/framework/cache/.test_writable" 2>/dev/null; then
        rm -f "storage/framework/cache/.test_writable"
        echo "✓ Cache storage is writable"
    else
        echo "ERROR: Cache storage is not writable"
        exit 1
    fi
fi

echo "Storage health check passed"
exit 0
