#!/bin/bash

###############################################################################
# restore.sh - Restore script for OGameXX database and files
# Supports selective restoration from backups
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
RESTORE_LOG_FILE="$BACKUP_DIR/restore.log"

# Logging functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$RESTORE_LOG_FILE"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$RESTORE_LOG_FILE"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$RESTORE_LOG_FILE"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$RESTORE_LOG_FILE"
}

# Initialize restore log
init_log() {
    mkdir -p "$BACKUP_DIR"
    echo "=== Restore started at $(date) ===" > "$RESTORE_LOG_FILE"
}

# Check prerequisites
check_prerequisites() {
    log_info "Checking prerequisites..."
    
    # Check if Docker is available
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed or not in PATH"
        exit 1
    fi
    
    # Check if database container is running
    if ! docker ps --format "{{.Names}}" | grep -q "^${DB_CONTAINER}$"; then
        log_error "Database container '$DB_CONTAINER' is not running"
        log_info "Please start the services first using ./deploy.sh"
        exit 1
    fi
    
    # Check if backup directory exists
    if [ ! -d "$BACKUP_DIR" ]; then
        log_error "Backup directory not found: $BACKUP_DIR"
        exit 1
    fi
    
    log_success "Prerequisites check passed"
}

# List available backups
list_backups() {
    log_info "Available backups:"
    echo ""
    
    local found_any=false
    
    # List database backups
    if [ -d "$BACKUP_DIR/database" ] && [ "$(ls -A $BACKUP_DIR/database 2>/dev/null)" ]; then
        echo -e "${GREEN}=== Database Backups ===${NC}"
        ls -lah "$BACKUP_DIR/database" | grep -E "\.(sql\.gz|sql)$" | \
            awk '{print $9 " (" $5 ") - " $6 " " $7 " " $8}' | \
            sed 's|.*/||' || true
        echo ""
        found_any=true
    fi
    
    # List file backups
    if [ -d "$BACKUP_DIR/files" ] && [ "$(ls -A $BACKUP_DIR/files 2>/dev/null)" ]; then
        echo -e "${GREEN}=== File Backups ===${NC}"
        ls -lah "$BACKUP_DIR/files" | grep -E "\.tar\.gz$" | \
            awk '{print $9 " (" $5 ") - " $6 " " $7 " " $8}' | \
            sed 's|.*/||' || true
        echo ""
        found_any=true
    fi
    
    # List full backups
    if [ -d "$BACKUP_DIR/full" ] && [ "$(ls -A $BACKUP_DIR/full 2>/dev/null)" ]; then
        echo -e "${GREEN}=== Full Backups ===${NC}"
        ls -lah "$BACKUP_DIR/full" | grep -E "\.tar\.gz$" | \
            awk '{print $9 " (" $5 ") - " $6 " " $7 " " $8}' | \
            sed 's|.*/||' || true
        echo ""
        found_any=true
    fi
    
    if [ "$found_any" = false ]; then
        log_warning "No backups found in $BACKUP_DIR"
    fi
}

# Show backup details
show_backup_details() {
    local backup_file=$1
    
    if [ ! -f "$backup_file" ]; then
        log_error "Backup file not found: $backup_file"
        return 1
    fi
    
    log_info "Backup Details:"
    echo ""
    echo -e "  ${GREEN}File:${NC} $(basename "$backup_file")"
    echo -e "  ${GREEN}Size:${NC} $(du -h "$backup_file" | cut -f1)"
    echo -e "  ${GREEN}Date:${NC} $(date -r "$backup_file")"
    
    # Check for metadata file
    local metadata_file="${backup_file}.meta"
    if [ -f "$metadata_file" ]; then
        echo -e "  ${GREEN}Type:${NC} $(grep -o '"type": "[^"]*"' "$metadata_file" | cut -d'"' -f4 || echo "unknown")"
        echo -e "  ${GREEN}Created:${NC} $(grep -o '"created_at": "[^"]*"' "$metadata_file" | cut -d'"' -f4 || echo "unknown")"
    fi
    echo ""
}

# Create database backup before restore
backup_before_restore() {
    log_info "Creating backup before restore..."
    
    local backup_name="pre_restore_$(date +%Y%m%d_%H%M%S)"
    local backup_path="$BACKUP_DIR/database/${backup_name}.sql"
    
    if docker exec "$DB_CONTAINER" mysqldump -u"$DB_USER" -p"$DB_PASS" \
        --single-transaction --routines --triggers \
        "$DB_NAME" > "$backup_path" 2>/dev/null; then
        
        log_success "Pre-restore backup created: $backup_path"
        return 0
    else
        log_error "Failed to create pre-restore backup"
        return 1
    fi
}

# Restore database
restore_database() {
    local backup_file=$1
    
    if [ ! -f "$backup_file" ]; then
        log_error "Database backup file not found: $backup_file"
        return 1
    fi
    
    log_info "Starting database restoration from: $(basename "$backup_file")"
    
    # Confirm restoration
    echo -e "${RED}WARNING: This will overwrite the current database!${NC}"
    read -p "Are you sure you want to continue? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        log_info "Restore cancelled by user"
        return 0
    fi
    
    # Create backup before restore
    backup_before_restore || {
        log_error "Cannot proceed without pre-restore backup"
        return 1
    }
    
    # Prepare restore command
    local restore_cmd=""
    if [[ "$backup_file" == *.gz ]]; then
        restore_cmd="gunzip -c \"$backup_file\" | docker exec -i \"$DB_CONTAINER\" mysql -u\"$DB_USER\" -p\"$DB_PASS\" \"$DB_NAME\""
    else
        restore_cmd="docker exec -i \"$DB_CONTAINER\" mysql -u\"$DB_USER\" -p\"$DB_PASS\" \"$DB_NAME\" < \"$backup_file\""
    fi
    
    # Execute restore
    if eval "$restore_cmd"; then
        log_success "Database restoration completed"
        
        # Clear Laravel caches
        log_info "Clearing Laravel caches..."
        docker exec ogamex-app php artisan cache:clear 2>/dev/null || true
        docker exec ogamex-app php artisan config:clear 2>/dev/null || true
        
        return 0
    else
        log_error "Database restoration failed"
        return 1
    fi
}

# Restore files
restore_files() {
    local backup_file=$1
    local target_dir="${2:-.}"
    
    if [ ! -f "$backup_file" ]; then
        log_error "File backup not found: $backup_file"
        return 1
    fi
    
    log_info "Starting file restoration from: $(basename "$backup_file")"
    
    # Confirm restoration
    echo -e "${RED}WARNING: This will overwrite existing files!${NC}"
    read -p "Are you sure you want to continue? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        log_info "Restore cancelled by user"
        return 0
    fi
    
    # Create backup of current files
    log_info "Creating backup of current files..."
    local current_backup="pre_restore_files_$(date +%Y%m%d_%H%M%S).tar.gz"
    tar -czf "$BACKUP_DIR/files/$current_backup" storage/app public/uploads public/media 2>/dev/null || true
    
    # Extract backup
    log_info "Extracting file backup..."
    if tar -xzf "$backup_file" -C "$target_dir" 2>/dev/null; then
        log_success "File restoration completed"
        
        # Set proper permissions
        log_info "Setting proper file permissions..."
        docker exec ogamex-app chown -R www-data:www-data storage public/uploads public/media 2>/dev/null || true
        docker exec ogamex-app chmod -R 775 storage public/uploads public/media 2>/dev/null || true
        
        return 0
    else
        log_error "File restoration failed"
        return 1
    fi
}

# Restore from full backup
restore_full() {
    local backup_file=$1
    
    if [ ! -f "$backup_file" ]; then
        log_error "Full backup not found: $backup_file"
        return 1
    fi
    
    log_info "Starting full restoration from: $(basename "$backup_file")"
    
    # Confirm restoration
    echo -e "${RED}WARNING: This will overwrite both database and files!${NC}"
    read -p "Are you sure you want to continue? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        log_info "Restore cancelled by user"
        return 0
    fi
    
    # Create temporary directory
    local temp_dir=$(mktemp -d)
    
    # Extract full backup
    log_info "Extracting full backup..."
    if tar -xzf "$backup_file" -C "$temp_dir" 2>/dev/null; then
        
        # Find database backup in archive
        local db_backup=$(find "$temp_dir" -name "*.sql" -o -name "*.sql.gz" | head -1)
        
        # Restore database if found
        if [ -n "$db_backup" ]; then
            log_info "Restoring database from full backup..."
            restore_database "$db_backup" || {
                log_error "Database restoration failed"
                rm -rf "$temp_dir"
                return 1
            }
        else
            log_warning "No database backup found in full backup"
        fi
        
        # Find file backup in archive
        local files_backup=$(find "$temp_dir" -name "*.tar.gz" | grep -v "$(basename "$backup_file")" | head -1)
        
        # Restore files if found
        if [ -n "$files_backup" ]; then
            log_info "Restoring files from full backup..."
            restore_files "$files_backup" "." || {
                log_error "File restoration failed"
                rm -rf "$temp_dir"
                return 1
            }
        else
            log_warning "No file backup found in full backup"
        fi
        
        # Cleanup
        rm -rf "$temp_dir"
        
        log_success "Full restoration completed"
        return 0
    else
        log_error "Failed to extract full backup"
        rm -rf "$temp_dir"
        return 1
    fi
}

# Interactive restore menu
interactive_restore() {
    echo ""
    echo "========================================================================="
    echo "                    $APP_NAME Restore Menu"
    echo "========================================================================="
    echo ""
    
    echo "What would you like to restore?"
    echo "1) Database only"
    echo "2) Files only"
    echo "3) Full restore (database + files)"
    echo "4) List available backups"
    echo "5) Cancel"
    echo ""
    
    read -p "Select option (1-5): " choice
    
    case $choice in
        1)
            echo ""
            echo "Available database backups:"
            list_backups | grep -A 20 "Database Backups" || true
            echo ""
            read -p "Enter database backup filename: " db_backup
            if [ -f "$BACKUP_DIR/database/$db_backup" ]; then
                show_backup_details "$BACKUP_DIR/database/$db_backup"
                restore_database "$BACKUP_DIR/database/$db_backup"
            elif [ -f "$BACKUP_DIR/full/$db_backup" ]; then
                show_backup_details "$BACKUP_DIR/full/$db_backup"
                restore_database "$BACKUP_DIR/full/$db_backup"
            else
                log_error "Backup file not found: $db_backup"
                exit 1
            fi
            ;;
        2)
            echo ""
            echo "Available file backups:"
            list_backups | grep -A 20 "File Backups" || true
            echo ""
            read -p "Enter file backup filename: " file_backup
            if [ -f "$BACKUP_DIR/files/$file_backup" ]; then
                show_backup_details "$BACKUP_DIR/files/$file_backup"
                restore_files "$BACKUP_DIR/files/$file_backup"
            elif [ -f "$BACKUP_DIR/full/$file_backup" ]; then
                show_backup_details "$BACKUP_DIR/full/$file_backup"
                restore_files "$BACKUP_DIR/full/$file_backup"
            else
                log_error "Backup file not found: $file_backup"
                exit 1
            fi
            ;;
        3)
            echo ""
            echo "Available full backups:"
            list_backups | grep -A 20 "Full Backups" || true
            echo ""
            read -p "Enter full backup filename: " full_backup
            if [ -f "$BACKUP_DIR/full/$full_backup" ]; then
                show_backup_details "$BACKUP_DIR/full/$full_backup"
                restore_full "$BACKUP_DIR/full/$full_backup"
            else
                log_error "Full backup not found: $full_backup"
                exit 1
            fi
            ;;
        4)
            list_backups
            ;;
        5)
            log_info "Restore cancelled"
            exit 0
            ;;
        *)
            log_error "Invalid option selected"
            exit 1
            ;;
    esac
}

# Show restore log
show_restore_log() {
    if [ -f "$RESTORE_LOG_FILE" ]; then
        echo ""
        echo "Restore Log:"
        echo "============"
        cat "$RESTORE_LOG_FILE"
    else
        log_warning "No restore log found"
    fi
}

# Main restore function
main() {
    echo ""
    echo "========================================================================="
    echo "                    $APP_NAME Restore Script"
    echo "========================================================================="
    echo ""
    
    init_log
    
    case "${1:-interactive}" in
        "interactive")
            interactive_restore
            ;;
        "database")
            if [ -z "$2" ]; then
                log_error "Please specify database backup file"
                log_info "Usage: $0 database <backup_file>"
                exit 1
            fi
            check_prerequisites
            show_backup_details "$2"
            restore_database "$2"
            ;;
        "files")
            if [ -z "$2" ]; then
                log_error "Please specify file backup file"
                log_info "Usage: $0 files <backup_file>"
                exit 1
            fi
            check_prerequisites
            show_backup_details "$2"
            restore_files "$2"
            ;;
        "full")
            if [ -z "$2" ]; then
                log_error "Please specify full backup file"
                log_info "Usage: $0 full <backup_file>"
                exit 1
            fi
            check_prerequisites
            show_backup_details "$2"
            restore_full "$2"
            ;;
        "list")
            list_backups
            ;;
        "log")
            show_restore_log
            ;;
        *)
            log_error "Invalid option: $1"
            log_info "Usage: $0 [interactive|database|files|full|list|log] [backup_file]"
            exit 1
            ;;
    esac
    
    show_restore_log
    
    echo ""
    echo "========================================================================="
    log_success "Restore process completed!"
    echo "========================================================================="
    echo ""
}

# Handle script interruption
trap 'log_error "Restore interrupted"; exit 1' INT TERM

# Run main function
main "$@"
