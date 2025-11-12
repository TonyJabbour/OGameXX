#!/bin/sh

set -euo pipefail  # Exit on any error, undefined variable, or pipe failure

# ==============================================
# DOCKER ENTRYPOINT SCRIPT
# ==============================================
# This script handles container initialization with improved error handling,
# Redis configuration, timezone settings, and container optimization.

# Set strict error handling
trap 'echo "[ERROR] Script failed at line $LINENO. Exit code: $?" >&2' ERR
trap 'echo "[INFO] Container shutdown complete"' EXIT

# Logging functions
log_info() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] [INFO] $*" >&2
}

log_warn() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] [WARN] $*" >&2
}

log_error() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] [ERROR] $*" >&2
}

log_debug() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] [DEBUG] $*" >&2
}

# Container role detection
role=${CONTAINER_ROLE:-none}
log_info "Container role: $role"

# ==============================================
# ENVIRONMENT SETUP
# ==============================================

# Set timezone and locale
set_timezone() {
    log_info "Setting timezone and locale configuration..."
    
    # Set timezone if specified
    TZ=${TZ:-UTC}
    export TZ
    echo "TZ=$TZ" > /etc/environment
    
    # Set locale if specified
    APP_LOCALE=${APP_LOCALE:-en_US.UTF-8}
    export LC_ALL=$APP_LOCALE
    export LANG=$APP_LOCALE
    export LANGUAGE=en_US:en
    
    log_info "Timezone set to: $TZ"
    log_info "Locale set to: $APP_LOCALE"
    
    # Ensure timezone database is available
    if [ -f /usr/share/zoneinfo/$TZ ]; then
        cp /usr/share/zoneinfo/$TZ /etc/localtime
        echo "$TZ" > /etc/timezone
        log_info "Timezone database updated successfully"
    else
        log_warn "Timezone $TZ not found, using UTC"
        cp /usr/share/zoneinfo/UTC /etc/localtime
        echo "UTC" > /etc/timezone
    fi
}

# Environment file setup
setup_env_file() {
    log_info "Checking for .env file..."
    
    if [ ! -f /var/www/.env ]; then
        if [ -f /var/www/.env.example ]; then
            cp /var/www/.env.example /var/www/.env
            log_info ".env file not found, copied .env.example to .env"
        elif [ -f /var/www/.env.docker ]; then
            cp /var/www/.env.docker /var/www/.env
            log_info ".env file not found, copied .env.docker to .env"
        else
            log_error "No .env, .env.example, or .env.docker files found. Please create an .env file." >&2
            exit 1
        fi
    else
        log_info "Using existing .env file"
    fi
    
    # Load environment variables
    set -a
    . /var/www/.env
    set +a
}

# Read secrets from docker secrets
setup_secrets() {
    log_info "Setting up secrets from Docker secrets..."
    
    # Database secrets
    if [ -f /run/secrets/db_root_password ]; then
        DB_PASSWORD=$(cat /run/secrets/db_root_password)
        export DB_PASSWORD
        log_info "Loaded database password from Docker secrets"
    fi
    
    # Redis secrets
    if [ -f /run/secrets/redis_password ]; then
        REDIS_PASSWORD=$(cat /run/secrets/redis_password)
        export REDIS_PASSWORD
        log_info "Loaded Redis password from Docker secrets"
    fi
    
    # Mail secrets
    if [ -f /run/secrets/smtp_password ]; then
        MAIL_PASSWORD=$(cat /run/secrets/smtp_password)
        export MAIL_PASSWORD
        log_info "Loaded SMTP password from Docker secrets"
    fi
}

# ==============================================
# REDIS CONFIGURATION
# ==============================================

configure_redis() {
    log_info "Configuring Redis connection..."
    
    # Wait for Redis to be available
    if [ -n "${REDIS_HOST:-}" ] && [ -n "${REDIS_PORT:-}" ]; then
        log_info "Waiting for Redis at $REDIS_HOST:$REDIS_PORT..."
        
        local max_attempts=30
        local attempt=1
        
        while [ $attempt -le $max_attempts ]; do
            if timeout 5 redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT}" ${REDIS_PASSWORD:+-a "$REDIS_PASSWORD"} ping >/dev/null 2>&1; then
                log_info "Redis connection successful"
                break
            fi
            
            if [ $attempt -eq $max_attempts ]; then
                log_error "Failed to connect to Redis after $max_attempts attempts"
                if [ "${REDIS_REQUIRE_AUTH:-false}" = "true" ]; then
                    exit 1
                else
                    log_warn "Continuing without Redis authentication as REDIS_REQUIRE_AUTH is not set to true"
                    break
                fi
            fi
            
            log_info "Redis not ready yet, waiting... (attempt $attempt/$max_attempts)"
            sleep 2
            attempt=$((attempt + 1))
        done
        
        # Test Redis connectivity
        if redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT}" ${REDIS_PASSWORD:+-a "$REDIS_PASSWORD"} ping >/dev/null 2>&1; then
            log_info "Redis connectivity test passed"
        else
            log_warn "Redis connectivity test failed, but continuing..."
        fi
        
        # Test database access if specified
        if [ -n "${REDIS_DB:-}" ]; then
            if redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT}" ${REDIS_PASSWORD:+-a "$REDIS_PASSWORD"} -n "${REDIS_DB}" ping >/dev/null 2>&1; then
                log_info "Redis database $REDIS_DB access successful"
            else
                log_warn "Failed to access Redis database $REDIS_DB"
            fi
        fi
        
    else
        log_warn "Redis host or port not configured, skipping Redis setup"
    fi
}

# Database connectivity check
check_database_connection() {
    if [ -n "${DB_HOST:-}" ] && [ -n "${DB_PORT:-}" ]; then
        log_info "Checking database connectivity..."
        
        local max_attempts=30
        local attempt=1
        
        while [ $attempt -le $max_attempts ]; do
            if timeout 5 mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME:-root}" ${DB_PASSWORD:+-p"$DB_PASSWORD"} >/dev/null 2>&1; then
                log_info "Database connection successful"
                return 0
            fi
            
            if [ $attempt -eq $max_attempts ]; then
                log_error "Failed to connect to database after $max_attempts attempts"
                exit 1
            fi
            
            log_info "Database not ready yet, waiting... (attempt $attempt/$max_attempts)"
            sleep 2
            attempt=$((attempt + 1))
        done
    else
        log_warn "Database host or port not configured"
    fi
}

# ==============================================
# GIT CONFIGURATION
# ==============================================

configure_git() {
    log_info "Configuring Git for container environment..."
    
    # Configure Git to trust the working directory
    git config --global --add safe.directory /var/www 2>/dev/null || true
    git config --global user.name "Docker Container" 2>/dev/null || true
    git config --global user.email "container@ogamex.local" 2>/dev/null || true
    
    log_info "Git configuration complete"
}

# ==============================================
# MAIN EXECUTION
# ==============================================

# Set up environment
set_timezone
setup_env_file
setup_secrets

# Extract environment information
is_production=false
if grep -q "^APP_ENV=production" .env 2>/dev/null || [ "${APP_ENV:-}" = "production" ]; then
    is_production=true
    log_info "Production environment detected"
else
    log_info "Development environment detected"
fi

# Role-based execution
case "$role" in
    "scheduler")
        log_info "Starting scheduler service..."
        configure_git
        check_database_connection
        
        while true; do
            log_info "Running scheduler tasks..."
            if php /var/www/html/artisan schedule:run --verbose --no-interaction 2>&1; then
                log_info "Scheduler run completed successfully"
            else
                log_error "Scheduler run failed"
            fi
            sleep 60
        done
        ;;
        
    "queue")
        log_info "Starting queue worker..."
        configure_git
        check_database_connection
        configure_redis
        
        # Queue worker configuration
        QUEUE_WORKER_CONCURRENCY=${QUEUE_WORKER_CONCURRENCY:-5}
        QUEUE_WORKER_TIMEOUT=${QUEUE_WORKER_TIMEOUT:-60}
        QUEUE_WORKER_MAX_TRIES=${QUEUE_WORKER_MAX_TRIES:-3}
        
        log_info "Starting queue worker with concurrency: $QUEUE_WORKER_CONCURRENCY, timeout: $QUEUE_WORKER_TIMEOUT"
        exec php /var/www/html/artisan queue:work \
            --verbose \
            --no-interaction \
            --tries="$QUEUE_WORKER_MAX_TRIES" \
            --timeout="$QUEUE_WORKER_TIMEOUT" \
            --concurrency="$QUEUE_WORKER_CONCURRENCY"
        ;;
        
    "app")
        log_info "Starting application service..."
        configure_git
        
        # Check dependencies
        check_database_connection
        configure_redis
        
        # Composer installation based on environment
        log_info "Installing dependencies..."
        if [ "$is_production" = true ]; then
            log_info "Production environment detected. Running composer install --no-dev..."
            if ! composer install --no-dev --optimize-autoloader --no-interaction; then
                log_error "Composer install failed"
                exit 1
            fi
        else
            log_info "Development environment detected. Running composer install..."
            if ! composer install --no-interaction; then
                log_error "Composer install failed"
                exit 1
            fi
        fi

        # Generate APP_KEY if not set or empty
        log_info "Checking APP_KEY configuration..."
        app_key=$(grep -E "^APP_KEY=" .env 2>/dev/null | cut -d '=' -f2 | tr -d '[:space:]' | tr -d '\r')
        if [ -z "$app_key" ]; then
            log_info "APP_KEY is empty or not set. Generating a new key..."
            if ! php artisan key:generate --force; then
                log_error "Failed to generate APP_KEY"
                exit 1
            fi
        else
            log_info "APP_KEY is configured"
        fi

        # Compile Rust modules if available
        if [ -f "./rust/compile.sh" ]; then
            log_info "Compiling Rust modules..."
            chmod +x ./rust/compile.sh
            if ! ./rust/compile.sh; then
                log_warn "Rust compilation failed or module not available"
            else
                log_info "Rust modules compiled successfully"
            fi
        else
            log_info "Rust compilation script not found, skipping..."
        fi

        # Run database migrations
        log_info "Running database migrations..."
        if ! php artisan migrate --force; then
            log_error "Database migrations failed"
            exit 1
        fi

        # Cache optimization for production
        if [ "$is_production" = true ]; then
            log_info "Production environment: Running cache optimizations..."
            
            # Clear existing caches
            php artisan cache:clear 2>/dev/null || true
            
            # Generate new caches
            if ! php artisan config:cache; then
                log_warn "Config cache generation failed"
            fi
            
            if ! php artisan route:cache; then
                log_warn "Route cache generation failed"
            fi
            
            if ! php artisan view:cache; then
                log_warn "View cache generation failed"
            fi
            
            log_info "Cache optimization complete"
        else
            log_info "Development environment: Skipping cache optimization"
        fi

        # Health check endpoint setup
        if [ "${HEALTH_CHECK_ENABLED:-false}" = "true" ]; then
            log_info "Health check endpoint enabled at ${HEALTH_CHECK_ENDPOINT:-/health}"
        fi

        log_info "Application setup complete, starting PHP-FPM..."
        exec php-fpm
        ;;
        
    *)
        log_error "Invalid container role: $role"
        log_error "Valid roles are: app, scheduler, queue"
        exit 1
        ;;
esac

log_info "Container initialization complete"