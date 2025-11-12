# Deployment Automation Scripts Usage Guide

This guide covers the three deployment automation scripts created for the OGameXX Docker setup.

## 📁 Scripts Overview

### 1. `deploy.sh` - One-Command Deployment
Handles complete application deployment including container building, service startup, migrations, and optimizations.

### 2. `backup.sh` - Automated Backup System
Provides comprehensive backup functionality for databases and files with retention policies.

### 3. `restore.sh` - Database and File Restoration
Supports selective restoration from backups with interactive and command-line interfaces.

---

## 🚀 deploy.sh - One-Command Deployment

### Usage
```bash
./deploy.sh [environment]
```

### Parameters
- `environment` (optional): Target environment - `production` (default) or `development`

### Features
- ✅ Prerequisites checking (Docker, Docker Compose)
- ✅ Environment file validation
- ✅ Automatic backup before deployment
- ✅ Container building with caching
- ✅ Service orchestration
- ✅ Health checks with timeout
- ✅ Database migration execution
- ✅ Laravel optimization (cache clearing, config caching)
- ✅ Service status monitoring
- ✅ Resource usage reporting
- ✅ Cleanup of old Docker resources
- ✅ Color-coded logging

### Example Usage
```bash
# Deploy to production (default)
./deploy.sh

# Deploy to development
./deploy.sh development

# If scripts aren't executable, run with bash
bash deploy.sh production
```

### What It Does
1. **Prerequisites Check**: Verifies Docker and Docker Compose installation
2. **Environment Validation**: Checks for `.env` file, creates from example if needed
3. **Pre-deployment Backup**: Automatically backs up database and files
4. **Container Management**: Stops old containers, builds new images, starts services
5. **Health Verification**: Waits for services to become healthy
6. **Database Setup**: Runs Laravel migrations
7. **Performance Optimization**: Clears and caches Laravel configurations
8. **Status Reporting**: Shows service health and resource usage
9. **Cleanup**: Removes unused Docker resources

### Output Example
```
=========================================================================
                    OGameXX Deployment Script
=========================================================================

[INFO] Starting deployment in production mode
[SUCCESS] Prerequisites check passed
[SUCCESS] Environment file check passed
[SUCCESS] Backup created successfully
[SUCCESS] Existing services stopped
[SUCCESS] Docker images built successfully
[SUCCESS] Services started
[SUCCESS] Services are healthy
[SUCCESS] Database migrations completed
[SUCCESS] Laravel optimizations completed
[SUCCESS] Deployment completed successfully!
=========================================================================
```

---

## 💾 backup.sh - Automated Backup System

### Usage
```bash
./backup.sh [type]
```

### Parameters
- `type` (optional): Backup type
  - `auto` (default): Full backup (database + files)
  - `database`: Database only
  - `files`: Files only
  - `full`: Full backup (database + files)
  - `list`: List all available backups
  - `stats`: Show backup statistics
  - `cleanup`: Remove old backups
  - `health`: Run health check

### Features
- ✅ **Database Backups**: MySQL dumps with compression
- ✅ **File Backups**: Storage directories (app, framework, logs, uploads)
- ✅ **Full Backups**: Combined database and files
- ✅ **Automated Compression**: Gzip compression for space efficiency
- ✅ **Retention Policies**: Automatic cleanup based on age
- ✅ **Backup Metadata**: JSON metadata with backup information
- ✅ **Health Monitoring**: Backup system health checks
- ✅ **Size Reporting**: Backup size calculations
- ✅ **Statistics Dashboard**: Backup usage statistics

### Retention Policies
- **Daily Backups**: Kept for 7 days (default)
- **Metadata Files**: Automatically cleaned up with backups
- **Automatic Cleanup**: Runs after each backup operation

### Backup Locations
```
./backups/
├── database/     # Database SQL dumps
├── files/        # File archives (tar.gz)
└── full/         # Combined backups
```

### Example Usage
```bash
# Full backup (default)
./backup.sh

# Database only
./backup.sh database

# Files only
./backup.sh files

# List all backups
./backup.sh list

# Show backup statistics
./backup.sh stats

# Clean up old backups
./backup.sh cleanup

# Check backup system health
./backup.sh health

# Run with bash if not executable
bash backup.sh auto
```

### Output Example
```
=========================================================================
                    OGameXX Backup Script
=========================================================================

[INFO] Starting full backup...
[SUCCESS] Database backup created: ./backups/database/db_server_20231113_120000.sql.gz
[INFO] Backup size: 2.3M
[SUCCESS] File backup created: ./backups/files/files_server_20231113_120000.tar.gz
[INFO] Backup size: 156M
[SUCCESS] Backup completed successfully!
=========================================================================
```

---

## 🔄 restore.sh - Database and File Restoration

### Usage
```bash
./restore.sh [mode] [backup_file]
```

### Parameters
- `mode` (optional): Restore mode
  - `interactive` (default): Interactive menu-driven restore
  - `database`: Restore database from specified file
  - `files`: Restore files from specified file
  - `full`: Restore full backup (database + files)
  - `list`: List available backups
  - `log`: Show restore log
- `backup_file` (required for non-interactive modes): Path to backup file

### Features
- ✅ **Interactive Menu**: User-friendly restore interface
- ✅ **Selective Restoration**: Choose database, files, or both
- ✅ **Pre-restore Backup**: Automatic backup before restoration
- ✅ **Safety Confirmations**: User confirmation for destructive operations
- ✅ **Backup Validation**: Verify backup file integrity
- ✅ **Metadata Display**: Show backup details before restore
- ✅ **Permission Management**: Automatically set correct file permissions
- ✅ **Laravel Integration**: Clear caches after database restore
- ✅ **Detailed Logging**: Complete restore operation logging
- ✅ **Error Handling**: Comprehensive error recovery

### Example Usage
```bash
# Interactive restore menu (recommended)
./restore.sh

# Restore database from specific file
./restore.sh database ./backups/database/db_server_20231113_120000.sql.gz

# Restore files from specific backup
./restore.sh files ./backups/files/files_server_20231113_120000.tar.gz

# Restore full backup
./restore.sh full ./backups/full/full_server_20231113_120000.tar.gz

# List available backups
./restore.sh list

# View restore log
./restore.sh log

# Run with bash if not executable
bash restore.sh interactive
```

### Interactive Menu
When running without parameters or with `interactive`, the script presents:
```
What would you like to restore?
1) Database only
2) Files only
3) Full restore (database + files)
4) List available backups
5) Cancel
```

### Safety Features
- **Pre-restore Backup**: Automatically creates backup before restoration
- **User Confirmation**: Requires explicit "yes" confirmation for destructive operations
- **Validation**: Checks backup file existence and integrity
- **Permission Fixes**: Automatically sets correct file permissions
- **Cache Clearing**: Clears Laravel caches after database restoration

### Output Example
```
=========================================================================
                    OGameXX Restore Script
=========================================================================

[INFO] Starting database restoration from: db_server_20231113_120000.sql.gz

Backup Details:
  File: db_server_20231113_120000.sql.gz
  Size: 2.3M
  Date: Mon Nov 13 12:00:00 2023
  Type: database
  Created: 2023-11-13T12:00:00

WARNING: This will overwrite the current database!
Are you sure you want to continue? (yes/no): yes
[SUCCESS] Database restoration completed
=========================================================================
```

---

## 🛠️ Setup and Configuration

### Prerequisites
- Docker and Docker Compose installed
- .env file configured (see .env.example)
- Sufficient disk space for backups (minimum 1GB recommended)

### Initial Setup
1. Ensure Docker is running
2. Copy `.env.example` to `.env` and configure
3. Run initial deployment:
   ```bash
   bash deploy.sh production
   ```
4. Test backup system:
   ```bash
   bash backup.sh health
   ```

### Environment Configuration
The `.env` file should be configured with:
- Database credentials
- Application settings
- Cache configuration
- Queue settings
- Mail configuration

### Storage Requirements
- **Backups**: Store in `./backups/` directory
- **Files**: Backup includes storage/app, storage/framework, storage/logs, public/uploads, public/media
- **Database**: MySQL/MariaDB dumps with compression

---

## 🔧 Troubleshooting

### Common Issues

#### Permission Denied
```bash
# If scripts aren't executable, run with bash
bash deploy.sh
bash backup.sh
bash restore.sh
```

#### Docker Not Running
```bash
# Check Docker status
docker ps

# Start Docker daemon (systemctl/d service)
sudo systemctl start docker  # Linux
# or use Docker Desktop for Mac/Windows
```

#### Database Connection Failed
```bash
# Check database container status
docker ps | grep ogamex-db

# Check database logs
docker logs ogamex-db
```

#### Backup Failures
```bash
# Run health check
bash backup.sh health

# Check available disk space
df -h ./backups

# Verify Docker is running
docker ps
```

#### Restore Failures
```bash
# Check restore log
bash restore.sh log

# Verify backup file exists
ls -la ./backups/database/
ls -la ./backups/files/

# Check database container is running
docker ps | grep ogamex-db
```

### Log Locations
- **Deploy Log**: Console output only
- **Backup Log**: `./backups/restore.log`
- **Docker Logs**: `docker logs <container_name>`

---

## 🔒 Security Considerations

### File Permissions
- Scripts set proper permissions automatically
- Database backups contain sensitive data
- Keep backup directory secure (chmod 700 recommended)

### Backup Security
- Database dumps include all data
- File backups include user uploads
- Implement encryption for sensitive environments
- Store backups in secure location

### Access Control
- Restrict access to backup directory
- Use strong passwords for database
- Implement SSL/TLS for production
- Regular security updates

---

## 📊 Monitoring and Maintenance

### Health Checks
```bash
# Backup system health
bash backup.sh health

# Service status
docker ps

# Application health
curl -f http://localhost/health || echo "Unhealthy"
```

### Maintenance Tasks
```bash
# Regular backups
# Add to crontab:
0 2 * * * /path/to/OGameXX/backup.sh auto

# Weekly cleanup
0 3 * * 0 /path/to/OGameXX/backup.sh cleanup

# Monthly restore test
0 4 1 * * /path/to/OGameXX/restore.sh list
```

### Performance Monitoring
- Check backup sizes regularly
- Monitor disk space usage
- Track service health
- Review error logs

---

## 🎯 Best Practices

### Deployment
- Always backup before deployment
- Test deployments in staging first
- Monitor logs during deployment
- Keep Docker images updated

### Backup Strategy
- Daily automated backups
- Weekly full backups
- Monthly restore testing
- Off-site backup storage
- Regular cleanup of old backups

### Restore Testing
- Test restore process monthly
- Verify data integrity after restore
- Document restore procedures
- Keep backup files organized

---

## 📞 Support

For issues or questions:
1. Check troubleshooting section
2. Review script logs
3. Verify Docker installation
4. Check system requirements

---

*Last updated: November 13, 2023*
