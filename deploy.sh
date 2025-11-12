#!/bin/bash

###############################################################################
# deploy.sh - One-command deployment script for OGameXX Docker setup
###############################################################################

set -e  # Exit on any error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="OGameXX"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
HEALTH_CHECK_TIMEOUT=300
BACKUP_BEFORE_DEPLOY=true
BACKUP_DIR="./backups"
ENV_FILE=".env"

# Default environment
ENVIRONMENT="${1:-production}"

# Logging functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker and Docker Compose are available
check_prerequisites() {
    log_info "Checking prerequisites..."
    
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed or not in PATH"
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        log_error "Docker Compose is not installed or not in PATH"
        exit 1
    fi
    
    log_success "Prerequisites check passed"
}

# Check if .env file exists
check_env_file() {
    log_info "Checking environment file..."
    
    if [ ! -f "$ENV_FILE" ]; then
        if [ -f "$ENV_FILE.example" ]; then
            log_warning ".env file not found, copying from .env.example"
            cp "$ENV_FILE.example" "$ENV_FILE"
            log_warning "Please edit $ENV_FILE with your configuration before continuing"
            read -p "Press enter to continue after editing .env file..."
        else
            log_error ".env file not found. Please create one based on your requirements"
            exit 1
        fi
    fi
    
    log_success "Environment file check passed"
}

# Create backup before deployment
create_backup() {
    if [ "$BACKUP_BEFORE_DEPLOY" = true ]; then
        log_info "Creating backup before deployment..."
        
        if [ -f "./backup.sh" ]; then
            ./backup.sh auto || {
                log_error "Backup failed"
                exit 1
            }
            log_success "Backup created successfully"
        else
            log_warning "backup.sh not found, skipping automated backup"
        fi
    fi
}

# Build Docker images
build_images() {
    log_info "Building Docker images..."
    
    if docker compose version &> /dev/null; then
        COMPOSE_CMD="docker compose"
    else
        COMPOSE_CMD="docker-compose"
    fi
    
    $COMPOSE_CMD -f "$COMPOSE_FILE" build --no-cache --parallel || {
        log_error "Failed to build Docker images"
        exit 1
    }
    
    log_success "Docker images built successfully"
}

# Stop existing services
stop_services() {
    log_info "Stopping existing services..."
    
    if docker compose version &> /dev/null; then
        COMPOSE_CMD="docker compose"
    else
        COMPOSE_CMD="docker-compose"
    fi
    
    $COMPOSE_CMD -f "$COMPOSE_FILE" down --remove-orphans || {
        log_error "Failed to stop existing services"
        exit 1
    }
    
    log_success "Existing services stopped"
}

# Start services
start_services() {
    log_info "Starting services..."
    
    if docker compose version &> /dev/null; then
        COMPOSE_CMD="docker compose"
    else
        COMPOSE_CMD="docker-compose"
    fi
    
    $COMPOSE_CMD -f "$COMPOSE_FILE" up -d || {
        log_error "Failed to start services"
        exit 1
    }
    
    log_success "Services started"
}

# Wait for services to be healthy
wait_for_health() {
    log_info "Waiting for services to be healthy..."
    
    local timeout=$HEALTH_CHECK_TIMEOUT
    local interval=5
    local elapsed=0
    
    while [ $elapsed -lt $timeout ]; do
        # Check if the app container is healthy
        if docker ps --filter "name=ogamex-app" --filter "health=healthy" --format "{{.Names}}" | grep -q "ogamex-app"; then
            log_success "Services are healthy"
            return 0
        fi
        
        log_info "Waiting for services to be healthy... ($elapsed/${timeout}s)"
        sleep $interval
        elapsed=$((elapsed + interval))
    done
    
    log_error "Services failed to become healthy within timeout period"
    return 1
}

# Run database migrations
run_migrations() {
    log_info "Running database migrations..."
    
    docker exec ogamex-app php artisan migrate --force || {
        log_error "Database migration failed"
        return 1
    }
    
    log_success "Database migrations completed"
}

# Run Laravel optimizations
optimize_laravel() {
    log_info "Running Laravel optimizations..."
    
    # Clear caches
    docker exec ogamex-app php artisan cache:clear || true
    docker exec ogamex-app php artisan config:cache || true
    docker exec ogamex-app php artisan route:cache || true
    docker exec ogamex-app php artisan view:cache || true
    
    log_success "Laravel optimizations completed"
}

# Show service status
show_status() {
    log_info "Service Status:"
    echo ""
    
    if docker compose version &> /dev/null; then
        COMPOSE_CMD="docker compose"
    else
        COMPOSE_CMD="docker-compose"
    fi
    
    $COMPOSE_CMD -f "$COMPOSE_FILE" ps
    echo ""
    
    # Show service health
    log_info "Service Health:"
    for container in ogamex-app ogamex-db ogamex-webserver; do
        if docker ps --format "{{.Names}}" | grep -q "^${container}$"; then
            status=$(docker inspect --format='{{.State.Health.Status}}' $container 2>/dev/null || echo "no-health-check")
            echo -e "  ${container}: ${status}"
        else
            echo -e "  ${container}: ${RED}stopped${NC}"
        fi
    done
    echo ""
    
    # Show resource usage
    log_info "Resource Usage:"
    docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}" \
        $(docker ps --format "{{.Names}}" | grep -E "ogamex-(app|db|webserver)") 2>/dev/null || true
}

# Show access URLs
show_urls() {
    log_info "Application Access URLs:"
    echo ""
    echo -e "  ${GREEN}Web Interface:${NC} http://localhost"
    echo -e "  ${GREEN}HTTPS:${NC} https://localhost (if SSL configured)"
    echo -e "  ${GREEN}PhpMyAdmin:${NC} http://localhost:8080"
    echo ""
    log_info "Database credentials:"
    echo -e "  ${GREEN}Host:${NC} ogame-db (internal) or localhost:3306 (external)"
    echo -e "  ${GREEN}Database:${NC} laravel"
    echo -e "  ${GREEN}Username:${NC} root"
    echo -e "  ${GREEN}Password:${NC} toor"
    echo ""
}

# Cleanup old Docker resources
cleanup() {
    log_info "Cleaning up old Docker resources..."
    
    # Remove unused images
    docker image prune -f || true
    
    # Remove unused volumes
    docker volume prune -f || true
    
    # Remove unused networks
    docker network prune -f || true
    
    log_success "Cleanup completed"
}

# Main deployment function
main() {
    echo ""
    echo "========================================================================="
    echo "                    $APP_NAME Deployment Script"
    echo "========================================================================="
    echo ""
    
    log_info "Starting deployment in $ENVIRONMENT mode"
    
    check_prerequisites
    check_env_file
    create_backup
    stop_services
    build_images
    start_services
    
    if wait_for_health; then
        run_migrations
        optimize_laravel
        show_status
        show_urls
        cleanup
        
        echo ""
        echo "========================================================================="
        log_success "Deployment completed successfully!"
        echo "========================================================================="
        echo ""
    else
        log_error "Deployment failed - services are not healthy"
        log_info "Check logs with: docker logs ogamex-app"
        exit 1
    fi
}

# Handle script interruption
trap 'log_error "Deployment interrupted"; exit 1' INT TERM

# Run main function
main
