#!/bin/bash

###############################################################################
# backup.sh - Automated backup script for OGameXX
# Supports database and file backups with retention policies
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
BACKUP_DIR="./backups"
DB_CONTAINER="ogamex-db"
DB_NAME="laravel"
DB_USER="root"
DB_PASS="toor"

# Retention policies (in days)
DAILY_RETENTION=7
WEEKLY_RETENTION=30
MONTHLY_RETENTION=365

# Backup types
BACKUP_DB=true
BACKUP_FILES=true
COMPRESS=true

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

# Check if backup directory exists
setup_backup_dir() {
    if [ ! -d "$BACKUP_DIR" ]; then
        log_info "Creating backup directory: $BACKUP_DIR"
        mkdir -p "$BACKUP_DIR"
    fi
    
    # Create subdirectories
    mkdir -p "$BACKUP_DIR/database"
    mkdir -p "$BACKUP_DIR/files"
    mkdir -p "$BACKUP_DIR/full"
}

# Generate backup filename with timestamp
generate_filename() {
    local prefix=$1
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local hostname=$(hostname -s)
    
    echo "${prefix}_${hostname}_${timestamp}"
}

# Check if database container is running
check_db_container() {
    if ! docker ps --format "{{.Names}}" | grep -q "^${DB_CONTAINER}$"; then
        log_error "Database container '$DB_CONTAINER' is not running"
        return 1
    fi
    
    log_info "Database container is running"
    return 0
}

# Create database backup
backup_database() {
    if [ "$BACKUP_DB" != true ]; then
        log_info "Skipping database backup"
        return 0
    fi
    
    log_info "Starting database backup..."
    
    local backup_name=$(generate_filename "db")
    local backup_path="$BACKUP_DIR/database/${backup_name}.sql"
    
    # Create database dump
    if docker exec "$DB_CONTAINER" mysqldump -u"$DB_USER" -p"$DB_PASS" \
        --single-transaction --routines --triggers \
        "$DB_NAME" > "$backup_path"; then
        
        if [ "$COMPRESS" = true ]; then
            log_info "Compressing database backup..."
            gzip "$backup_path"
            backup_path="${backup_path}.gz"
        fi
        
        log_success "Database backup created: $backup_path"
        
        # Calculate backup size
        local size=$(du -h "$backup_path" | cut -f1)
        log_info "Backup size: $size"
        
        # Create metadata file
        create_backup_metadata "$backup_path" "database"
        
        return 0
    else
        log_error "Database backup failed"
        return 1
    fi
}

# Create file backup
backup_files() {
    if [ "$BACKUP_FILES" != true ]; then
        log_info "Skipping file backup"
        return 0
    fi
    
    log_info "Starting file backup..."
    
    local backup_name=$(generate_filename "files")
    local backup_path="$BACKUP_DIR/files/${backup_name}.tar.gz"
    
    # Create list of directories to backup
    local dirs_to_backup=(
        "storage/app"
        "storage/framework"
        "storage/logs"
        "public/uploads"
        "public/media"
    )
    
    local backup_sources=""
    for dir in "${dirs_to_backup[@]}"; do
        if [ -d "$dir" ]; then
            backup_sources="$backup_sources $dir"
        fi
    done
    
    if [ -z "$backup_sources" ]; then
        log_warning "No files found to backup"
        return 0
    fi
    
    # Create tar archive
    if tar -czf "$backup_path" $backup_sources 2>/dev/null; then
        log_success "File backup created: $backup_path"
        
        # Calculate backup size
        local size=$(du -h "$backup_path" | cut -f1)
        log_info "Backup size: $size"
        
        # Create metadata file
        create_backup_metadata "$backup_path" "files"
        
        return 0
    else
        log_error "File backup failed"
        return 1
    fi
}

# Create full backup (database + files)
backup_full() {
    log_info "Starting full backup..."
    
    local backup_name=$(generate_filename "full")
    local backup_path="$BACKUP_DIR/full/${backup_name}.tar.gz"
    
    local temp_dir=$(mktemp -d)
    local db_backup_file=""
    local files_backup_file=""
    
    # Create database backup
    if [ "$BACKUP_DB" = true ]; then
        local db_backup_name=$(generate_filename "db")
        db_backup_file="$temp_dir/${db_backup_name}.sql"
        
        if docker exec "$DB_CONTAINER" mysqldump -u"$DB_USER" -p"$DB_PASS" \
            --single-transaction --routines --triggers \
            "$DB_NAME" > "$db_backup_file"; then
            log_success "Database backup created for full backup"
        else
            log_error "Database backup failed for full backup"
            rm -rf "$temp_dir"
            return 1
        fi
    fi
    
    # Create file backup
    if [ "$BACKUP_FILES" = true ]; then
        local files_backup_name=$(generate_filename "files")
        files_backup_file="$temp_dir/${files_backup_name}.tar.gz"
        
        local dirs_to_backup=(
            "storage/app"
            "storage/framework"
            "storage/logs"
            "public/uploads"
            "public/media"
        )
        
        local backup_sources=""
        for dir in "${dirs_to_backup[@]}"; do
            if [ -d "$dir" ]; then
                backup_sources="$backup_sources $dir"
            fi
        done
        
        if [ -n "$backup_sources" ]; then
            tar -czf "$files_backup_file" $backup_sources 2>/dev/null
            log_success "File backup created for full backup"
        fi
    fi
    
    # Create archive with database and files
    if tar -czf "$backup_path" -C "$temp_dir" . 2>/dev/null; then
        log_success "Full backup created: $backup_path"
        
        # Calculate backup size
        local size=$(du -h "$backup_path" | cut -f1)
        log_info "Backup size: $size"
        
        # Create metadata file
        create_backup_metadata "$backup_path" "full"
        
        # Cleanup temp directory
        rm -rf "$temp_dir"
        
        return 0
    else
        log_error "Full backup failed"
        rm -rf "$temp_dir"
        return 1
    fi
}

# Create backup metadata
create_backup_metadata() {
    local backup_path=$1
    local backup_type=$2
    
    local metadata_file="${backup_path}.meta"
    
    cat > "$metadata_file" << EOF
{
    "type": "$backup_type",
    "path": "$backup_path",
    "size": "$(du -h "$backup_path" | cut -f1)",
    "created_at": "$(date -Iseconds)",
    "hostname": "$(hostname)",
    "app_name": "$APP_NAME",
    "database": "$DB_NAME",
    "container": "$DB_CONTAINER"
}
EOF
    
    log_info "Metadata created: $metadata_file"
}

# List backups
list_backups() {
    log_info "Available backups:"
    echo ""
    
    for type in database files full; do
        local dir="$BACKUP_DIR/$type"
        if [ -d "$dir" ] && [ "$(ls -A $dir 2>/dev/null)" ]; then
            echo -e "${GREEN}=== $type backups ===${NC}"
            ls -lah "$dir" | grep -E "\.(sql\.gz|tar\.gz)$" || true
            echo ""
        fi
    done
}

# Cleanup old backups based on retention policy
cleanup_old_backups() {
    log_info "Cleaning up old backups..."
    
    # Get current date for comparison
    local current_date=$(date +%s)
    
    # Cleanup daily backups (older than DAILY_RETENTION days)
    if [ $DAILY_RETENTION -gt 0 ]; then
        find "$BACKUP_DIR" -type f -name "*.sql.gz" -o -name "*.tar.gz" \
            -mtime +$DAILY_RETENTION -delete 2>/dev/null || true
        
        find "$BACKUP_DIR" -type f -name "*.meta" -mtime +$DAILY_RETENTION -delete 2>/dev/null || true
    fi
    
    # Cleanup metadata files without corresponding backups
    find "$BACKUP_DIR" -type f -name "*.meta" | while read meta_file; do
        local backup_file="${meta_file%.meta}"
        if [ ! -f "$backup_file" ]; then
            rm -f "$meta_file"
        fi
    done
    
    log_success "Cleanup completed"
}

# Show backup statistics
show_backup_stats() {
    log_info "Backup Statistics:"
    echo ""
    
    local total_size=$(du -sh "$BACKUP_DIR" 2>/dev/null | cut -f1 || echo "0")
    echo -e "  ${GREEN}Total backup size:${NC} $total_size"
    
    # Count backups by type
    local db_count=$(find "$BACKUP_DIR/database" -type f -name "*.sql.gz" 2>/dev/null | wc -l || echo "0")
    local files_count=$(find "$BACKUP_DIR/files" -type f -name "*.tar.gz" 2>/dev/null | wc -l || echo "0")
    local full_count=$(find "$BACKUP_DIR/full" -type f -name "*.tar.gz" 2>/dev/null | wc -l || echo "0")
    
    echo -e "  ${GREEN}Database backups:${NC} $db_count"
    echo -e "  ${GREEN}File backups:${NC} $files_count"
    echo -e "  ${GREEN}Full backups:${NC} $full_count"
    echo ""
    
    # Show latest backups
    log_info "Latest backups:"
    find "$BACKUP_DIR" -type f -name "*.sql.gz" -o -name "*.tar.gz" | \
        sort | tail -5 | while read file; do
            local size=$(du -h "$file" | cut -f1)
            local date=$(date -r "$file" "+%Y-%m-%d %H:%M")
            echo -e "  $(basename "$file"): ${size} (${date})"
        done
}

# Run health check
health_check() {
    log_info "Running backup system health check..."
    
    # Check if backup directory is writable
    if [ ! -w "$BACKUP_DIR" ]; then
        log_error "Backup directory is not writable: $BACKUP_DIR"
        return 1
    fi
    
    # Check if Docker is available
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed or not in PATH"
        return 1
    fi
    
    # Check if database container is running
    if ! check_db_container; then
        return 1
    fi
    
    log_success "Health check passed"
    return 0
}

# Main backup function
run_backup() {
    local backup_type="${1:-full}"
    
    echo ""
    echo "========================================================================="
    echo "                    $APP_NAME Backup Script"
    echo "========================================================================="
    echo ""
    
    setup_backup_dir
    
    case $backup_type in
        "database")
            check_db_container && backup_database
            ;;
        "files")
            backup_files
            ;;
        "full")
            check_db_container && backup_full
            ;;
        "auto")
            log_info "Running automatic backup (full)..."
            check_db_container && backup_full
            ;;
        *)
            log_error "Invalid backup type: $backup_type"
            log_info "Available types: database, files, full, auto"
            exit 1
            ;;
    esac
    
    cleanup_old_backups
    show_backup_stats
    
    echo ""
    echo "========================================================================="
    log_success "Backup completed successfully!"
    echo "========================================================================="
    echo ""
}

# Handle script interruption
trap 'log_error "Backup interrupted"; exit 1' INT TERM

# Parse command line arguments
case "${1:-auto}" in
    "list")
        setup_backup_dir
        list_backups
        ;;
    "stats")
        setup_backup_dir
        show_backup_stats
        ;;
    "cleanup")
        setup_backup_dir
        cleanup_old_backups
        log_success "Cleanup completed"
        ;;
    "health")
        health_check
        ;;
    *)
        run_backup "$1"
        ;;
esac
