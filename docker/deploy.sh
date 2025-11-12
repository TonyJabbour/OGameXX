#!/bin/bash

# ==============================================
# DOCKER DEPLOYMENT SCRIPT
# ==============================================
# This script demonstrates how to deploy the OGameX application
# using the Docker environment configuration with secrets

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $*"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $*"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $*"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $*"
}

# Configuration
COMPOSE_FILE="docker-compose.secrets.yml"
ENV_FILE=".env.docker"
SECRETS_DIR="docker/secrets"

# ==============================================
# FUNCTIONS
# ==============================================

check_prerequisites() {
    log_info "Checking prerequisites..."
    
    # Check if Docker is installed
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed"
        exit 1
    fi
    
    # Check if Docker Compose is installed
    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        log_error "Docker Compose is not installed"
        exit 1
    fi
    
    # Check if required files exist
    if [ ! -f "$COMPOSE_FILE" ]; then
        log_error "Compose file $COMPOSE_FILE not found"
        exit 1
    fi
    
    if [ ! -f "$ENV_FILE" ]; then
        log_warn "Environment file $ENV_FILE not found, creating from example..."
        if [ -f ".env.example" ]; then
            cp .env.example .env
        else
            log_error "No environment file template found"
            exit 1
        fi
    fi
    
    log_success "Prerequisites check passed"
}

setup_secrets() {
    log_info "Setting up Docker secrets..."
    
    # Check if secrets directory exists
    if [ ! -d "$SECRETS_DIR" ]; then
        log_warn "Secrets directory $SECRETS_DIR not found, creating..."
        mkdir -p "$SECRETS_DIR"/{database,redis,mail,external,monitoring}
    fi
    
    # Check for required secret files
    local required_secrets=(
        "$SECRETS_DIR/database/root_password"
        "$SECRETS_DIR/redis/password"
    )
    
    for secret in "${required_secrets[@]}"; do
        if [ ! -f "$secret" ]; then
            log_warn "Secret file $secret not found, creating with placeholder..."
            case "$secret" in
                *"root_password")
                    echo "CHANGE_ME_db_root_password" > "$secret"
                    ;;
                *"password")
                    echo "CHANGE_ME_redis_password" > "$secret"
                    ;;
            esac
            chmod 600 "$secret"
        fi
    done
    
    # Set proper permissions
    find "$SECRETS_DIR" -type f -name "*" -not -name "README.md" -not -name ".gitignore" -exec chmod 600 {} \; 2>/dev/null || true
    
    log_success "Secrets setup complete"
}

validate_environment() {
    log_info "Validating environment configuration..."
    
    # Check timezone setting
    if [ -z "${TZ:-}" ]; then
        log_warn "TZ environment variable not set, using UTC"
        export TZ=UTC
    fi
    
    # Validate required environment variables
    local required_vars=(
        "APP_NAME"
        "APP_ENV"
        "APP_URL"
    )
    
    for var in "${required_vars[@]}"; do
        if [ -z "${!var:-}" ]; then
            log_warn "Environment variable $var is not set"
        fi
    done
    
    log_success "Environment validation complete"
}

deploy() {
    log_info "Starting deployment..."
    
    # Create required directories
    mkdir -p storage/{app,framework/{cache,sessions,views},logs}
    mkdir -p bootstrap/cache
    
    # Set proper permissions
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    
    # Build and start services
    log_info "Building and starting services..."
    if command -v docker-compose &> /dev/null; then
        docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up --build -d
    else
        docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up --build -d
    fi
    
    log_success "Services started successfully"
}

wait_for_services() {
    log_info "Waiting for services to be ready..."
    
    local max_attempts=60
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker ps --filter "name=ogamex-app" --filter "status=running" | grep -q ogamex-app; then
            log_success "Application service is running"
            break
        fi
        
        if [ $attempt -eq $max_attempts ]; then
            log_error "Services did not start within timeout"
            exit 1
        fi
        
        log_info "Waiting for services... (attempt $attempt/$max_attempts)"
        sleep 5
        attempt=$((attempt + 1))
    done
}

run_migrations() {
    log_info "Running database migrations..."
    
    # Execute migrations in the app container
    if docker exec ogamex-app php artisan migrate --force; then
        log_success "Database migrations completed"
    else
        log_error "Database migrations failed"
        exit 1
    fi
}

setup_application() {
    log_info "Setting up application..."
    
    # Generate APP_KEY if needed
    if ! docker exec ogamex-app php artisan key:generate --force; then
        log_warn "APP_KEY generation failed or already set"
    fi
    
    # Cache optimization for production
    if [ "${APP_ENV:-}" = "production" ]; then
        log_info "Running cache optimization..."
        docker exec ogamex-app php artisan config:cache || log_warn "Config cache failed"
        docker exec ogamex-app php artisan route:cache || log_warn "Route cache failed"
        docker exec ogamex-app php artisan view:cache || log_warn "View cache failed"
    fi
    
    log_success "Application setup complete"
}

show_status() {
    log_info "Service status:"
    
    # Show running containers
    if command -v docker-compose &> /dev/null; then
        docker-compose -f "$COMPOSE_FILE" ps
    else
        docker compose -f "$COMPOSE_FILE" ps
    fi
    
    echo
    log_info "Application URLs:"
    echo "  Web Server: http://localhost"
    echo "  MailHog UI: http://localhost:8025"
    echo "  PhpMyAdmin: http://localhost:8080"
    echo
    
    # Show health check if enabled
    if [ "${HEALTH_CHECK_ENABLED:-false}" = "true" ]; then
        log_info "Health Check:"
        echo "  Endpoint: ${HEALTH_CHECK_ENDPOINT:-/health}"
    fi
}

cleanup() {
    log_info "Cleaning up..."
    
    # Stop and remove containers
    if command -v docker-compose &> /dev/null; then
        docker-compose -f "$COMPOSE_FILE" down
    else
        docker compose -f "$COMPOSE_FILE" down
    fi
    
    log_success "Cleanup complete"
}

# ==============================================
# MAIN SCRIPT
# ==============================================

main() {
    local action="${1:-deploy}"
    
    case "$action" in
        "deploy")
            log_info "Starting Docker deployment..."
            check_prerequisites
            setup_secrets
            validate_environment
            deploy
            wait_for_services
            run_migrations
            setup_application
            show_status
            log_success "Deployment complete!"
            ;;
        "stop")
            log_info "Stopping services..."
            if command -v docker-compose &> /dev/null; then
                docker-compose -f "$COMPOSE_FILE" stop
            else
                docker compose -f "$COMPOSE_FILE" stop
            fi
            log_success "Services stopped"
            ;;
        "restart")
            log_info "Restarting services..."
            if command -v docker-compose &> /dev/null; then
                docker-compose -f "$COMPOSE_FILE" restart
            else
                docker compose -f "$COMPOSE_FILE" restart
            fi
            wait_for_services
            log_success "Services restarted"
            ;;
        "status")
            show_status
            ;;
        "logs")
            log_info "Showing logs..."
            if command -v docker-compose &> /dev/null; then
                docker-compose -f "$COMPOSE_FILE" logs -f
            else
                docker compose -f "$COMPOSE_FILE" logs -f
            fi
            ;;
        "cleanup")
            cleanup
            ;;
        "migrate")
            run_migrations
            ;;
        "setup")
            setup_application
            ;;
        "secrets")
            setup_secrets
            ;;
        "help"|"-h"|"--help")
            echo "Docker Deployment Script"
            echo ""
            echo "Usage: $0 [command]"
            echo ""
            echo "Commands:"
            echo "  deploy    - Full deployment (default)"
            echo "  stop      - Stop all services"
            echo "  restart   - Restart all services"
            echo "  status    - Show service status"
            echo "  logs      - Show service logs"
            echo "  cleanup   - Stop and remove containers"
            echo "  migrate   - Run database migrations"
            echo "  setup     - Setup application"
            echo "  secrets   - Setup secrets"
            echo "  help      - Show this help"
            echo ""
            ;;
        *)
            log_error "Unknown command: $action"
            log_info "Use '$0 help' for available commands"
            exit 1
            ;;
    esac
}

# Handle script arguments
main "$@"
