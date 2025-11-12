# OGameX Docker Deployment Guide

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Quick Start Guide](#quick-start-guide)
4. [Environment Setup](#environment-setup)
5. [Development vs Production](#development-vs-production)
6. [Service Management](#service-management)
7. [Database Operations](#database-operations)
8. [Backup and Restore](#backup-and-restore)
9. [Troubleshooting](#troubleshooting)
10. [Security Best Practices](#security-best-practices)
11. [Monitoring and Health Checks](#monitoring-and-health-checks)
12. [Performance Optimization](#performance-optimization)

---

## Overview

OGameX is a modern Laravel application designed for high-performance gaming with a comprehensive Docker-based deployment architecture. This guide provides everything developers and DevOps engineers need to deploy, manage, and optimize OGameX using Docker.

### Key Features
- **Multi-container architecture** with Redis, MariaDB, PHP-FPM, and Nginx
- **Development and production** configurations
- **Automated deployment** and backup scripts
- **Health monitoring** and performance optimization
- **Security hardening** and best practices
- **Queue workers** and task scheduling
- **Xdebug support** for development

---

## Architecture

### Container Overview

The OGameX Docker architecture consists of the following services:

```
┌─────────────────────────────────────────────────────────────┐
│                        OGameX Architecture                  │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Nginx     │  │   Redis     │  │  MariaDB    │         │
│  │  (Web)      │  │ (Cache)     │  │ (Database)  │         │
│  │   :80/:443  │  │   :6379     │  │   :3306     │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│         │                │                │                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │    PHP      │  │   Queue     │  │  Scheduler  │         │
│  │  (FPM)      │  │   Worker    │  │             │         │
│  │    :9000    │  │             │  │             │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│         │                │                │                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │ PhpMyAdmin  │  │   MinIO     │  │Elasticsearch│         │
│  │  (Dev)      │  │ (S3)        │  │ (Search)    │         │
│  │   :8080     │  │  :9000      │  │   :9200     │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Service Descriptions

#### Core Services

**PHP-FPM (`ogamex-app`)**
- **Purpose**: Application server processing PHP requests
- **Ports**: 9000 (FPM), 9001 (debug in development)
- **Role**: Handles Laravel application logic
- **Features**: OPcache, health checks, multi-stage builds

**Nginx (`ogamex-webserver`)**
- **Purpose**: Reverse proxy and web server
- **Ports**: 80 (HTTP), 443 (HTTPS)
- **Features**: SSL termination, load balancing, static file serving

**MariaDB (`ogamex-db`)**
- **Purpose**: Primary database
- **Port**: 3306 (development only)
- **Features**: Optimized configuration, health checks, backup support

**Redis (`ogamex-redis`)**
- **Purpose**: Caching and session storage
- **Port**: 6379 (development only)
- **Features**: Memory optimization, persistence

#### Additional Services (Development)

**Queue Worker (`ogamex-queue-worker`)**
- **Purpose**: Processes background jobs
- **Scaling**: Can be scaled horizontally
- **Monitoring**: Health checks and resource monitoring

**Scheduler (`ogamex-scheduler`)**
- **Purpose**: Runs Laravel task scheduler
- **Function**: Executes scheduled tasks and cron jobs

**PhpMyAdmin (`ogamex-phpmyadmin`)**
- **Purpose**: Database administration interface
- **Access**: Web UI on port 8080

**MinIO (`ogamex-minio`)**
- **Purpose**: S3-compatible object storage
- **Ports**: 9000 (API), 9001 (Console)
- **Features**: File storage and management

**Elasticsearch (`ogamex-elasticsearch`)**
- **Purpose**: Full-text search engine
- **Port**: 9200
- **Features**: Search indexing and querying

**MailHog (`ogamex-mailhog`)**
- **Purpose**: Email testing interface
- **Ports**: 1025 (SMTP), 8025 (Web UI)

### Network Architecture

All services communicate through the `app-network` bridge network:

```yaml
networks:
  app-network:
    driver: bridge
    driver_opts:
      com.docker.network.bridge.host_binding_ipv4: "0.0.0.0"  # Development only
```

### Volume Management

**Persistent Volumes:**
- `ogame-dbdata`: MariaDB data persistence
- `ogame-redisdata`: Redis data persistence
- `ogame-miniodata`: MinIO data persistence
- `ogame-elasticsearchdata`: Elasticsearch data persistence

**Development Volumes:**
- Source code mounted with `cached` strategy for performance
- Live reload support for development

---

## Quick Start Guide

### One-Command Deployment

For immediate deployment, use the provided script:

```bash
# Development deployment
./deploy.sh development

# Production deployment
./deploy.sh production
```

### Manual Development Setup

#### Prerequisites
- Docker Desktop installed and running
- Docker Compose installed
- At least 4GB RAM available
- 10GB free disk space

#### Step 1: Clone and Setup
```bash
git clone <repository-url>
cd OGameXX

# Copy environment file
cp .env.example .env

# Edit environment variables
nano .env
```

#### Step 2: Start Services
```bash
# Start all services in detached mode
docker-compose up -d

# Check service status
docker-compose ps

# View logs
docker-compose logs -f ogamex-app
```

#### Step 3: Install Dependencies
```bash
# Install PHP dependencies
docker-compose exec ogamex-app composer install

# Install Node.js dependencies
docker-compose exec ogamex-app npm install

# Generate application key
docker-compose exec ogamex-app php artisan key:generate

# Run database migrations
docker-compose exec ogamex-app php artisan migrate

# Seed database (optional)
docker-compose exec ogamex-app php artisan db:seed
```

#### Step 4: Build Frontend Assets
```bash
# Development build
docker-compose exec ogamex-app npm run dev

# Production build
docker-compose exec ogamex-app npm run build
```

#### Step 5: Access Application
- **Main Application**: http://localhost
- **Alternative Port**: http://localhost:8080
- **PhpMyAdmin**: http://localhost:8081
- **MailHog**: http://localhost:8025

### Verification

Verify the deployment is working:

```bash
# Check container health
docker-compose ps

# Test application endpoint
curl -I http://localhost

# Check Laravel status
docker-compose exec ogamex-app php artisan about

# Verify database connection
docker-compose exec ogamex-app php artisan tinker
> DB::connection()->getPdo();
```

---

## Environment Setup

### Environment Configuration Files

#### `.env` (Production)
```bash
# Application
APP_NAME="OGameX"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=ogame-db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=your_secure_password

# Redis
REDIS_HOST=ogame-redis
REDIS_PORT=6379
REDIS_PASSWORD=

# Cache and Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (Production)
MAIL_MAILER=smtp
MAIL_HOST=your.smtp.host
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Security
APP_KEY=base64:generated-key-here
```

#### `.env.local` (Development)
```bash
# Application (Development)
APP_NAME="OGameX"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database (Development)
DB_CONNECTION=mysql
DB_HOST=ogame-db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=toor

# Cache (Development - File based)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail (Development - MailHog)
MAIL_MAILER=smtp
MAIL_HOST=ogamex-mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="test@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Xdebug (Development)
XDEBUG_MODE=develop,debug
XDEBUG_CONFIG="client_host=host.docker.internal"

# Redis (Optional for development)
REDIS_HOST=ogamex-redis
REDIS_PORT=6379
```

### Docker Compose Configuration

#### Base Configuration (`docker-compose.yml`)
Core services for all environments with health checks and dependencies.

#### Development Override (`docker-compose.override.yml`)
Adds development-specific services and configuration:
- Xdebug enabled
- MailHog for email testing
- MinIO for S3-compatible storage
- Elasticsearch for search functionality
- Extended port mappings
- Live reload support

#### Production Configuration (`docker-compose.prod.yml`)
Production-optimized services:
- OPcache enabled
- Production-only services
- No exposed development ports
- Optimized resource limits
- Security hardening

### Environment Variables Reference

#### Application Variables
| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `APP_NAME` | Application name | OGameX | Yes |
| `APP_ENV` | Environment (local/production) | local | Yes |
| `APP_DEBUG` | Debug mode (true/false) | true | Yes |
| `APP_URL` | Application URL | http://localhost | Yes |
| `APP_KEY` | Laravel encryption key | Generated | Yes |

#### Database Variables
| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `DB_HOST` | Database host | ogame-db | Yes |
| `DB_PORT` | Database port | 3306 | Yes |
| `DB_DATABASE` | Database name | laravel | Yes |
| `DB_USERNAME` | Database user | root | Yes |
| `DB_PASSWORD` | Database password | toor | Yes |

#### Redis Variables
| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `REDIS_HOST` | Redis host | ogame-redis | Yes |
| `REDIS_PORT` | Redis port | 6379 | Yes |
| `REDIS_PASSWORD` | Redis password | null | No |

#### Cache and Queue Variables
| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `CACHE_DRIVER` | Cache driver (redis/file) | file | Yes |
| `SESSION_DRIVER` | Session driver (redis/file) | file | Yes |
| `QUEUE_CONNECTION` | Queue connection (redis/sync) | sync | Yes |

---

## Development vs Production

### Key Differences

| Aspect | Development | Production |
|--------|-------------|------------|
| **Debug Mode** | Enabled (`APP_DEBUG=true`) | Disabled (`APP_DEBUG=false`) |
| **OPcache** | Disabled for live reload | Enabled for performance |
| **Log Level** | `debug` | `error` |
| **Cache Driver** | `file` (filesystem) | `redis` (memory) |
| **Session Driver** | `file` (filesystem) | `redis` (memory) |
| **Queue Connection** | `sync` (synchronous) | `redis` (asynchronous) |
| **Mail Driver** | `log` or MailHog | SMTP server |
| **Xdebug** | Enabled for debugging | Disabled |
| **SSL** | Self-signed certificate | Valid SSL certificate |
| **Ports** | Exposed for development | Minimal exposure |

### Development Environment

**Purpose**: Local development and testing

**Features**:
- Xdebug enabled for step-by-step debugging
- Hot reload for instant code changes
- MailHog for email testing
- MinIO for S3-compatible storage
- Elasticsearch for search functionality
- Verbose logging for debugging
- PhpMyAdmin for database management

**Startup Command**:
```bash
# Use base compose file with development overrides
docker-compose up -d

# Or explicitly use development configuration
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
```

**Access URLs**:
- Application: http://localhost
- Application (Dev): http://localhost:8080
- PhpMyAdmin: http://localhost:8081
- MailHog: http://localhost:8025
- MinIO Console: http://localhost:9001
- Elasticsearch: http://localhost:9200

### Production Environment

**Purpose**: Live deployment

**Features**:
- OPcache enabled for optimal performance
- Redis for caching and sessions
- Asynchronous queue processing
- SSL/TLS encryption
- Resource limits and monitoring
- Log rotation and management
- Security hardening

**Startup Command**:
```bash
# Use production-specific compose file
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Or use the deployment script
./deploy.sh production
```

**Access URLs**:
- Application: https://yourdomain.com
- Database: Internal network only (not exposed)

### Environment Switching

#### Switch to Development
```bash
# Stop current services
docker-compose down

# Use development configuration
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d

# Or set environment
export COMPOSE_FILE=docker-compose.yml:docker-compose.override.yml
docker-compose up -d
```

#### Switch to Production
```bash
# Stop current services
docker-compose down

# Use production configuration
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Or set environment
export COMPOSE_FILE=docker-compose.yml:docker-compose.prod.yml
docker-compose up -d
```

---

## Service Management

### Starting Services

#### All Services
```bash
# Start all services in detached mode
docker-compose up -d

# Start with build (rebuild images)
docker-compose up -d --build

# Start specific services
docker-compose up -d ogamex-app ogamex-webserver ogamex-db
```

#### Selective Starting
```bash
# Start only web services
docker-compose up -d ogamex-webserver ogamex-app

# Start with dependencies
docker-compose up -d --no-deps ogamex-webserver
```

### Stopping Services

#### Graceful Shutdown
```bash
# Stop all services
docker-compose stop

# Stop specific services
docker-compose stop ogamex-app ogamex-queue-worker

# Stop and remove containers
docker-compose down

# Stop and remove with volumes (⚠️ Data loss)
docker-compose down -v
```

### Restarting Services

#### Service-Specific Restart
```bash
# Restart single service
docker-compose restart ogamex-app

# Restart with rebuild
docker-compose restart --build ogamex-app

# Restart dependencies
docker-compose up -d --force-recreate ogamex-app
```

#### Full Environment Restart
```bash
# Stop all services
docker-compose down

# Start all services fresh
docker-compose up -d --build --force-recreate
```

### Service Status

#### Check Status
```bash
# List all containers
docker-compose ps

# Detailed status with health checks
docker-compose ps -a

# Service health
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
```

#### Check Logs
```bash
# View logs for specific service
docker-compose logs ogamex-app

# Follow logs in real-time
docker-compose logs -f ogamex-app

# Last 100 lines
docker-compose logs --tail=100 ogamex-app

# Logs with timestamp
docker-compose logs -t ogamex-app

# All services
docker-compose logs
```

#### Monitor Resources
```bash
# Resource usage
docker stats

# Specific containers
docker stats ogamex-app ogamex-db ogamex-webserver

# Non-streaming output
docker stats --no-stream
```

### Container Access

#### Execute Commands
```bash
# Access PHP container shell
docker-compose exec ogamex-app /bin/bash

# Run single command
docker-compose exec ogamex-app php artisan list

# Database shell
docker-compose exec ogamex-db mysql -u root -p

# Redis CLI
docker-compose exec ogamex-redis redis-cli
```

#### File Operations
```bash
# Copy files from container
docker cp ogamex-app:/var/www/storage/logs ./logs

# Copy files to container
docker cp ./config/ ogamex-app:/var/www/config

# Sync volumes (development)
docker run --rm -v $(pwd):/source -v ogamex-app:/target alpine \
    rsync -av /source/ /target/
```

### Scaling Services

#### Queue Workers
```bash
# Scale queue workers
docker-compose up -d --scale ogamex-queue-worker=3

# Check scaled services
docker-compose ps
```

#### Load Balancing
```bash
# Multiple app containers with Nginx load balancing
docker-compose up -d --scale ogamex-app=3

# Nginx will automatically load balance between instances
```

### Updating Services

#### Rolling Updates
```bash
# Update without downtime
docker-compose pull  # Pull new images
docker-compose up -d  # Rolling restart

# Update specific service
docker-compose up -d --no-deps --build ogamex-app
```

#### Database Updates
```bash
# Run migrations
docker-compose exec ogamex-app php artisan migrate

# Migration status
docker-compose exec ogamex-app php artisan migrate:status

# Rollback (if needed)
docker-compose exec ogamex-app php artisan migrate:rollback
```

---

## Database Operations

### Connection Management

#### Database Credentials
```
Host: ogame-db (internal) or localhost:3306 (external)
Database: laravel
Username: root
Password: toor (development) / your_secure_password (production)
```

#### Connection Testing
```bash
# Test from PHP container
docker-compose exec ogamex-app php artisan tinker
> DB::connection()->getPdo();

# Direct MySQL connection
docker-compose exec ogamex-db mysql -u root -p laravel

# Test from host machine
mysql -h 127.0.0.1 -P 3306 -u root -p laravel
```

### Database Administration

#### Using PhpMyAdmin
Access via web interface: http://localhost:8081

**Features**:
- Visual database management
- SQL query interface
- Table structure editing
- Data import/export
- User management

**Login Credentials**:
- Server: ogame-db
- Username: root
- Password: toor

#### Command Line Operations

**Access MySQL Shell**:
```bash
docker-compose exec ogamex-db mysql -u root -p

# With database selection
docker-compose exec ogamex-db mysql -u root -p laravel
```

**Database Information**:
```sql
-- Show databases
SHOW DATABASES;

-- Show tables
USE laravel;
SHOW TABLES;

-- Table structure
DESCRIBE users;

-- Row count
SELECT COUNT(*) FROM users;
```

### Laravel Database Operations

#### Migrations
```bash
# Run pending migrations
docker-compose exec ogamex-app php artisan migrate

# Force migration (production)
docker-compose exec ogamex-app php artisan migrate --force

# Fresh migration with seeding
docker-compose exec ogamex-app php artisan migrate:fresh --seed

# Rollback last batch
docker-compose exec ogamex-app php artisan migrate:rollback

# Check migration status
docker-compose exec ogamex-app php artisan migrate:status

# Create migration
docker-compose exec ogamex-app php artisan make:migration add_users_table
```

#### Seeds and Factories
```bash
# Run all seeders
docker-compose exec ogamex-app php artisan db:seed

# Run specific seeder
docker-compose exec ogamex-app php artisan db:seed UserSeeder

# Fresh migration with seeding
docker-compose exec ogamex-app php artisan migrate:fresh --seed

# Create seeder
docker-compose exec ogamex-app php artisan make:seeder UserSeeder
```

#### Data Management
```bash
# Database console
docker-compose exec ogamex-app php artisan tinker

# Clear database
docker-compose exec ogamex-app php artisan db:wipe

# Show connection info
docker-compose exec ogamex-app php artisan db:show

# Database size
docker-compose exec ogamex-app php artisan db:table users
```

### Database Maintenance

#### Performance Monitoring
```bash
# Show process list
docker-compose exec ogamex-db mysql -u root -p -e "SHOW PROCESSLIST;"

# Show table status
docker-compose exec ogamex-db mysql -u root -p -e "SHOW TABLE STATUS;"

# Index analysis
docker-compose exec ogamex-app php artisan db:show --counts --sizes
```

#### Optimization
```bash
# Optimize all tables
docker-compose exec ogamex-db mysql -u root -p -e "USE laravel; OPTIMIZE TABLE users, planets, fleets;"

# Analyze tables
docker-compose exec ogamex-db mysql -u root -p -e "USE laravel; ANALYZE TABLE users, planets, fleets;"

# Check table integrity
docker-compose exec ogamex-db mysql -u root -p -e "USE laravel; CHECK TABLE users, planets, fleets;"
```

#### Log Analysis
```bash
# Error log
docker-compose exec ogamex-db tail -f /var/log/mysql/error.log

# Slow query log
docker-compose exec ogamex-db tail -f /var/log/mysql/slow.log

# General query log
docker-compose exec ogamex-db mysql -u root -p -e "SET GLOBAL general_log = 'ON';"
```

---

## Backup and Restore

### Automated Backup System

OGameX includes a comprehensive backup system supporting multiple backup types and retention policies.

#### Backup Types

**Database Backup**
- Complete MySQL database dump
- Includes tables, views, stored procedures, and triggers
- Compression support (gzip)
- Metadata generation

**File Backup**
- Application storage directories
- User uploads and media files
- Configuration files (non-sensitive)
- Log files

**Full Backup**
- Combined database and file backup
- Single archive for complete system state
- Consistent point-in-time snapshot

### Using the Backup Script

#### Basic Backup Operations
```bash
# Create full backup (database + files)
./backup.sh full

# Database only
./backup.sh database

# Files only
./backup.sh files

# Automatic backup (same as full)
./backup.sh auto
```

#### Backup Management
```bash
# List all backups
./backup.sh list

# Show backup statistics
./backup.sh stats

# Cleanup old backups
./backup.sh cleanup

# Health check of backup system
./backup.sh health
```

#### Backup Configuration

Edit the backup script configuration:
```bash
# Backup retention (in days)
DAILY_RETENTION=7
WEEKLY_RETENTION=30
MONTHLY_RETENTION=365

# Backup options
BACKUP_DB=true
BACKUP_FILES=true
COMPRESS=true

# Backup directory
BACKUP_DIR="./backups"
```

### Manual Database Backup

#### Using mysqldump
```bash
# Direct database dump
docker-compose exec ogamex-db mysqldump -u root -p laravel > backup.sql

# Compressed backup
docker-compose exec ogamex-db mysqldump -u root -p laravel | gzip > backup.sql.gz

# Include all databases
docker-compose exec ogamex-db mysqldump -u root -p --all-databases > all_databases.sql

# Schema only (no data)
docker-compose exec ogamex-db mysqldump -u root -p --no-data laravel > schema.sql

# Data only (no schema)
docker-compose exec ogamex-db mysqldump -u root -p --no-create-info laravel > data.sql
```

#### Advanced mysqldump Options
```bash
# Single transaction (for InnoDB)
docker-compose exec ogamex-db mysqldump -u root -p \
    --single-transaction --routines --triggers \
    laravel > backup.sql

# Where clause for specific data
docker-compose exec ogamex-db mysqldump -u root -p \
    --where="created_at >= '2024-01-01'" \
    laravel users > users_2024.sql

# Specific tables
docker-compose exec ogamex-db mysqldump -u root -p \
    laravel users planets galaxies > specific_tables.sql
```

### Manual File Backup

#### Using tar
```bash
# Backup storage directories
tar -czf files_backup.tar.gz storage/app public/uploads storage/logs

# Specific directories
tar -czf user_files.tar.gz public/uploads public/media storage/app/public

# Exclude unwanted files
tar -czf clean_backup.tar.gz \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    storage/ public/uploads
```

#### Rsync Backup
```bash
# Sync to external location
rsync -avz --delete storage/ /backup/ogamex/storage/
rsync -avz --delete public/uploads/ /backup/ogamex/uploads/

# With progress and logging
rsync -avz --delete --progress \
    storage/ /backup/ogamex/storage/ \
    > /var/log/backup_storage.log 2>&1
```

### Restore Operations

#### Database Restore

**From Automated Backup**:
```bash
# List available database backups
ls backups/database/

# Restore from compressed backup
gunzip -c backups/database/db_hostname_20241113_120000.sql.gz | \
    docker-compose exec -T ogamex-db mysql -u root -p laravel

# Restore from plain SQL
docker-compose exec -T ogamex-db mysql -u root -p laravel < backups/database/db_hostname_20241113_120000.sql
```

**Manual Restore**:
```bash
# Create database first (if needed)
docker-compose exec ogamex-db mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS laravel_restored;"

# Restore database
docker-compose exec -T ogamex-db mysql -u root -p laravel_restored < backup.sql

# Update .env to use restored database
# DB_DATABASE=laravel_restored
```

**Point-in-Time Recovery**:
```bash
# Full backup restore
gunzip -c backups/full/full_hostname_20241113_120000.tar.gz | tar -xz

# Restore database
gunzip -c db_hostname_20241113_120000.sql.gz | \
    docker-compose exec -T ogamex-db mysql -u root -p laravel

# Restore files
tar -xzf files_hostname_20241113_120000.tar.gz
```

#### File Restore
```bash
# Restore from tar backup
tar -xzf files_backup.tar.gz -C /

# Restore specific directory
tar -xzf files_backup.tar.gz -C / storage/app

# Overwrite existing files
tar -xzf files_backup.tar.gz -C / --overwrite
```

### Scheduled Backups

#### Cron Setup
```bash
# Edit crontab
crontab -e

# Add backup jobs
# Daily backup at 2 AM
0 2 * * * /path/to/OGameXX/backup.sh auto

# Weekly full backup on Sunday at 1 AM
0 1 * * 0 /path/to/OGameXX/backup.sh full

# Monthly backup on 1st at 3 AM
0 3 1 * * /path/to/OGameXX/backup.sh full
```

#### Docker Volume Backup
```bash
# Backup Docker volumes directly
docker run --rm -v ogame-dbdata:/data -v $(pwd):/backup \
    alpine tar -czf /backup/mariadb_data.tar.gz -C /data .

# Restore Docker volumes
docker run --rm -v ogame-dbdata:/data -v $(pwd):/backup \
    alpine tar -xzf /backup/mariadb_data.tar.gz -C /data
```

### Backup Verification

#### Test Backups
```bash
# Verify database backup integrity
gunzip -t backups/database/db_*.sql.gz

# Verify file backup integrity
tar -tzf backups/files/files_*.tar.gz > /dev/null

# Test restore to temporary database
gunzip -c backups/database/db_*.sql.gz | \
    docker-compose exec -T ogamex-db mysql -u root -p test_restore
```

#### Backup Monitoring
```bash
# Check backup sizes over time
ls -lah backups/database/ | tail -10
ls -lah backups/files/ | tail -10

# Monitor backup disk usage
du -sh backups/

# Alert on failed backups
./backup.sh auto || echo "Backup failed!" | mail -s "OGameX Backup Alert" admin@domain.com
```

### Backup Best Practices

#### Backup Strategy
1. **Daily Incremental**: Database changes
2. **Weekly Full**: Complete system backup
3. **Monthly Archive**: Long-term retention
4. **Off-site Copy**: Remote storage or cloud
5. **Testing**: Regular restore testing

#### Security Considerations
- Encrypt sensitive backups
- Secure backup storage locations
- Limit backup file permissions
- Regular security audits of backup access

#### Performance Considerations
- Schedule backups during low-traffic periods
- Use compression to save space
- Monitor backup duration and performance
- Consider backup impact on production systems

---

## Troubleshooting

### Common Issues and Solutions

#### Container Won't Start

**Symptoms**: Container exits immediately or fails to start

**Diagnosis**:
```bash
# Check container status
docker-compose ps

# Check logs for errors
docker-compose logs ogamex-app

# Inspect container configuration
docker inspect ogamex-app
```

**Solutions**:

*Port Conflicts*:
```bash
# Check port usage
lsof -i :80
lsof -i :3306

# Kill process using port
kill -9 $(lsof -t -i:80)

# Use different ports
docker-compose up -d -p "8080:80"
```

*Permission Issues*:
```bash
# Fix Laravel storage permissions
docker-compose exec ogamex-app chown -R www-data:www-data /var/www/storage
docker-compose exec ogamex-app chmod -R 775 /var/www/storage

# Fix Docker socket permissions (Linux)
sudo usermod -aG docker $USER
newgrp docker
```

*Missing Environment Variables*:
```bash
# Check environment file
docker-compose exec ogamex-app env | grep APP_

# Validate .env file syntax
docker-compose config
```

#### Database Connection Issues

**Symptoms**: "Connection refused" or "Access denied" errors

**Diagnosis**:
```bash
# Check database container status
docker-compose ps ogamex-db

# Test database connectivity
docker-compose exec ogamex-app php artisan tinker
> DB::connection()->getPdo();

# Check database logs
docker-compose logs ogamex-db
```

**Solutions**:

*Database Not Ready*:
```bash
# Wait for database to be healthy
docker-compose up -d ogamex-db
sleep 30
docker-compose exec ogamex-app php artisan migrate
```

*Wrong Credentials*:
```bash
# Check environment variables
grep DB_ .env

# Test manual connection
docker-compose exec ogamex-db mysql -u root -p

# Reset database password
docker-compose exec ogamex-db mysqladmin -u root password 'newpassword'
```

*Network Issues*:
```bash
# Check network connectivity
docker network ls
docker network inspect ogamexx_app-network

# Test from another container
docker-compose exec ogamex-app ping ogame-db
```

#### Performance Issues

**Symptoms**: Slow response times, high CPU/memory usage

**Diagnosis**:
```bash
# Check resource usage
docker stats

# Check system resources
docker system df
free -h
df -h

# Check Laravel performance
docker-compose exec ogamex-app php artisan route:list --path=api
docker-compose exec ogamex-app php artisan config:show
```

**Solutions**:

*Clear Laravel Caches*:
```bash
docker-compose exec ogamex-app php artisan cache:clear
docker-compose exec ogamex-app php artisan config:clear
docker-compose exec ogamex-app php artisan route:clear
docker-compose exec ogamex-app php artisan view:clear
```

*Optimize Images*:
```bash
# Remove unused images
docker image prune -a

# Rebuild with no cache
docker-compose build --no-cache

# Optimize Docker build
docker build --compress --squash -t ogamex-app .
```

*Scale Services*:
```bash
# Scale queue workers
docker-compose up -d --scale ogamex-queue-worker=3

# Increase PHP-FPM workers
# Edit php/local.ini: pm.max_children = 50
```

#### File Permission Issues

**Symptoms**: "Permission denied" errors when writing files

**Diagnosis**:
```bash
# Check current permissions
docker-compose exec ogamex-app ls -la /var/www/storage

# Check user ownership
docker-compose exec ogamex-app id

# Check volume mounts
docker volume ls
docker volume inspect ogamexx_ogame-dbdata
```

**Solutions**:
```bash
# Fix Laravel storage permissions
docker-compose exec ogamex-app chown -R www-data:www-data /var/www/storage
docker-compose exec ogamex-app chmod -R 775 /var/www/storage

# Fix bootstrap/cache permissions
docker-compose exec ogamex-app chown -R www-data:www-data /var/www/bootstrap/cache
docker-compose exec ogamex-app chmod -R 775 /var/www/bootstrap/cache

# Fix public uploads permissions
docker-compose exec ogamex-app chown -R www-data:www-data /var/www/public/uploads
docker-compose exec ogamex-app chmod -R 755 /var/www/public/uploads
```

#### Memory Issues

**Symptoms**: Containers killed by OOM (Out of Memory), system slowdown

**Diagnosis**:
```bash
# Check memory usage
docker stats --no-stream

# Check system memory
free -h
cat /proc/meminfo

# Check container memory limits
docker inspect ogamex-app | grep -A 5 Memory
```

**Solutions**:

*Add Memory Limits*:
```yaml
# Add to docker-compose.yml
services:
  ogamex-app:
    mem_limit: 1g
    mem_reservation: 512m
    
  ogamex-db:
    mem_limit: 2g
    mem_reservation: 1g
```

*Optimize PHP Configuration*:
```ini
# php/local.ini
memory_limit = 256M
max_execution_time = 60
post_max_size = 32M
upload_max_filesize = 16M
```

*Scale Resources*:
```bash
# Add more memory to Docker Desktop
# Restart Docker after changing settings
docker system prune -a
```

### Debugging Techniques

#### Container Inspection
```bash
# Detailed container information
docker inspect ogamex-app

# Container logs with timestamps
docker logs -t ogamex-app

# Follow logs from multiple containers
docker-compose logs -f --tail=100

# Real-time container events
docker events --filter container=ogamex-app
```

#### Network Debugging
```bash
# List networks
docker network ls

# Inspect network
docker network inspect ogamexx_app-network

# Test connectivity between containers
docker-compose exec ogamex-app ping ogame-db
docker-compose exec ogamex-app nc -zv ogame-db 3306

# DNS resolution test
docker-compose exec ogamex-app nslookup ogame-db
```

#### Process Debugging
```bash
# Check running processes
docker-compose exec ogamex-app ps aux

# Check PHP-FPM status
docker-compose exec ogamex-app ps aux | grep php-fpm

# Check system processes
docker-compose exec ogamex-app top

# Check open files
docker-compose exec ogamex-app lsof
```

#### Log Analysis
```bash
# Laravel application logs
docker-compose exec ogamex-app tail -f /var/www/storage/logs/laravel.log

# Nginx access logs
docker-compose exec ogamex-webserver tail -f /var/log/nginx/access.log

# Nginx error logs
docker-compose exec ogamex-webserver tail -f /var/log/nginx/error.log

# PHP-FPM logs
docker-compose exec ogamex-app tail -f /var/log/php8.4-fpm.log

# MariaDB logs
docker-compose exec ogamex-db tail -f /var/log/mysql/error.log
```

### Performance Profiling

#### Laravel Profiling
```bash
# Enable debug mode (development only)
echo "APP_DEBUG=true" >> .env

# Use Laravel Telescope (if installed)
docker-compose exec ogamex-app php artisan telescope:install
docker-compose exec ogamex-app php artisan migrate

# Check slow queries
docker-compose exec ogamex-app php artisan db:show --slow
```

#### Database Profiling
```bash
# Enable slow query log
docker-compose exec ogamex-db mysql -u root -p -e \
    "SET GLOBAL slow_query_log = 'ON';"

# Check query performance
docker-compose exec ogamex-db mysql -u root -p -e \
    "SHOW PROCESSLIST;"

# Analyze table usage
docker-compose exec ogamex-app php artisan db:table users --counts
```

### Recovery Procedures

#### Data Recovery
```bash
# Restore from backup
./backup.sh list
gunzip -c backups/database/db_*.sql.gz | \
    docker-compose exec -T ogamex-db mysql -u root -p laravel

# Reset application state
docker-compose exec ogamex-app php artisan migrate:fresh --seed

# Clear all caches
docker-compose exec ogamex-app php artisan cache:clear
```

#### System Recovery
```bash
# Complete rebuild
docker-compose down -v
docker-compose up -d --build

# Reset permissions
docker-compose exec ogamex-app chown -R www-data:www-data /var/www

# Restart services
docker-compose restart
```

---

## Security Best Practices

### Container Security

#### Non-Root Execution
```dockerfile
# Dockerfile security hardening
FROM php:8.4-fpm

# Create non-root user
RUN groupadd -r www-app && useradd -r -g www-app www-app

# Set proper ownership
COPY --chown=www-app:www-app . /var/www/

# Use non-root user
USER www-app

# Disable root shell access
USER nobody
```

#### Minimal Attack Surface
```dockerfile
# Remove unnecessary packages
RUN apt-get update && apt-get install -y \
    --no-install-recommends \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Remove package manager
RUN rm -rf /var/lib/dpkg /var/lib/rpm /var/cache/apt/*
```

#### Security Scanning
```bash
# Scan images for vulnerabilities
docker run --rm -v /var/run/docker.sock:/var/run/docker.sock \
    -v $(pwd):/root/.cache/ \
    aquasec/trivy image ogamex-app:latest

# Scan Docker Compose configuration
docker run --rm -i hadolint/hadolint < Dockerfile
```

### Network Security

#### Network Isolation
```yaml
# docker-compose.yml - Network security
networks:
  app-network:
    driver: bridge
    internal: false  # Set to true for production
    driver_opts:
      com.docker.network.driver.mtu: 1400

  database-network:
    driver: bridge
    internal: true  # No external access
    driver_opts:
      com.docker.network.driver.mtu: 1400
```

#### Firewall Rules
```bash
# Allow only necessary ports (Ubuntu/Debian)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 3306/tcp  # Database port

# Allow internal Docker networks
sudo ufw allow from 172.18.0.0/16
```

#### Secrets Management
```yaml
# Use Docker secrets in production
services:
  ogamex-app:
    secrets:
      - db_password
      - app_key

secrets:
  db_password:
    file: ./secrets/db_password.txt
  app_key:
    file: ./secrets/app_key.txt
```

### Application Security

#### Environment Security
```bash
# .env file security (production)
# Generate strong APP_KEY
php artisan key:generate --force

# Use strong database passwords
DB_PASSWORD=$(openssl rand -base64 32)

# Disable debug mode
APP_DEBUG=false

# Set secure session settings
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

#### HTTPS Configuration
```nginx
# nginx/conf.d/ssl.conf
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    
    add_header Strict-Transport-Security "max-age=63072000" always;
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
}
```

#### Input Validation
```php
// Laravel request validation
public function store(Request $request)
{
    $request->validate([
        'username' => 'required|string|min:3|max:50|alpha_dash',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'galaxy' => 'required|integer|min:1|max:99',
        'system' => 'required|integer|min:1|max:99',
        'planet' => 'required|integer|min:1|max:15'
    ]);
    
    // Continue with validated data
}
```

### Database Security

#### Database User Management
```sql
-- Create application user with limited privileges
CREATE USER 'ogamex_app'@'%' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON laravel.* TO 'ogamex_app'@'%';
FLUSH PRIVILEGES;

-- Remove root remote access
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove test database
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
```

#### Database Encryption
```yaml
# MariaDB encryption at rest
# my.cnf
[mariadb]
innodb_encrypt_tables=ON
innodb_encrypt_log=ON
innodb_encrypt_temporary_tables=ON
innodb_encrypt_thr_threshold=1
innodb_file_per_table=1
encrypt_tmp_disk_keys=ON
```

### File System Security

#### Permission Hardening
```bash
# Secure file permissions
find /var/www -type f -exec chmod 644 {} \;
find /var/www -type d -exec chmod 755 {} \;

# Secure Laravel directories
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache

# Secure configuration files
chmod 600 /var/www/.env
chown www-data:www-data /var/www/.env
```

#### Upload Security
```php
// Laravel upload validation
public function upload(Request $request)
{
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,gif|max:2048|dimensions:max_width=200,max_height=200'
    ]);
    
    $path = $request->file('avatar')->store('avatars', 'public');
    
    // Additional security checks
    $imageInfo = getimagesize(storage_path('app/public/' . $path));
    if ($imageInfo === false) {
        unlink(storage_path('app/public/' . $path));
        throw new \Exception('Invalid image file');
    }
}
```

### Monitoring and Alerting

#### Security Monitoring
```bash
# Monitor failed login attempts
docker-compose exec ogamex-webserver tail -f /var/log/nginx/access.log | \
    grep "POST /login" | grep " 401 "

# Monitor file changes
find /var/www -type f -newermt "1 hour ago" -exec ls -la {} \;

# Monitor suspicious processes
docker-compose exec ogamex-app ps aux | grep -v "php-fpm\|nginx\|cron"
```

#### Log Security
```php
// Laravel logging configuration
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 14,  // Auto-cleanup old logs
    ],
    
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'info',
        'days' => 30,
    ],
],
```

### Security Auditing

#### Regular Security Checks
```bash
# Container security audit
docker run --rm -v /var/run/docker.sock:/var/run/docker.sock \
    -v $(pwd):/root/.cache/ \
    aquasec/trivy fs /var/www

# Docker Compose security scan
docker-compose config --quiet

# Check for updates
docker-compose pull
docker-compose up -d --force-recreate

# Security headers check
curl -I https://yourdomain.com | grep -E "(X-|Content-Security-Policy|Strict-Transport-Security)"
```

#### Compliance Checklist
- [ ] Containers run as non-root users
- [ ] Unnecessary ports are not exposed
- [ ] Environment variables contain no secrets
- [ ] SSL/TLS is configured and enforced
- [ ] Database has limited user privileges
- [ ] File permissions are properly set
- [ ] Regular security updates are applied
- [ ] Monitoring and alerting is configured
- [ ] Backups are encrypted and secured
- [ ] Network segmentation is implemented

---

## Monitoring and Health Checks

### Health Check System

OGameX implements comprehensive health monitoring across all services with automatic recovery and alerting.

#### Container Health Checks

**PHP Application Health Check**:
```bash
# Built-in health check
docker-compose exec ogamex-app curl -f http://localhost:9000/health || exit 1

# Custom Laravel health check
docker-compose exec ogamex-app php artisan about
```

**Database Health Check**:
```bash
# MySQL/MariaDB health check
docker-compose exec ogamex-db mariadb-admin ping -h localhost

# Connection test
docker-compose exec ogamex-app php artisan tinker --execute="DB::connection()->getPdo();"
```

**Redis Health Check**:
```bash
# Redis ping test
docker-compose exec ogamex-redis redis-cli ping

# Connection test
docker-compose exec ogamex-app php artisan tinker --execute="Cache::store('redis')->put('health', 'ok', 60);"
```

**Nginx Health Check**:
```bash
# HTTP health check
curl -f http://localhost/health || exit 1

# Nginx configuration test
docker-compose exec ogamex-webserver nginx -t
```

### Health Check Configuration

#### Docker Compose Health Checks
```yaml
services:
  ogamex-app:
    healthcheck:
      test: ["CMD-SHELL", "curl -f http://localhost:9000/health || exit 1"]
      interval: 10s
      timeout: 6s
      retries: 60
      start_period: 30s

  ogamex-db:
    healthcheck:
      test: ["CMD", "mariadb-admin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 3s
      retries: 5
      start_period: 30s

  ogamex-redis:
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 3s
      retries: 5
      start_period: 10s

  ogamex-webserver:
    healthcheck:
      test: ["CMD-SHELL", "curl -f http://localhost/health || exit 1"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
```

### Monitoring Tools

#### Resource Monitoring

**Docker Stats**:
```bash
# Real-time resource usage
docker stats

# Specific containers
docker stats ogamex-app ogamex-db ogamex-webserver

# JSON output for scripting
docker stats --format "{{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}" --no-stream
```

**System Resources**:
```bash
# System load
uptime

# Memory usage
free -h

# Disk usage
df -h

# CPU information
lscpu
```

#### Application Monitoring

**Laravel Telescope** (Development):
```bash
# Install Telescope
docker-compose exec ogamex-app composer require laravel/telescope --dev
docker-compose exec ogamex-app php artisan telescope:install
docker-compose exec ogamex-app php artisan migrate

# Access at http://localhost/telescope
```

**Laravel Horizon** (Queue Monitoring):
```bash
# Install Horizon
docker-compose exec ogamex-app composer require laravel/horizon
docker-compose exec ogamex-app php artisan horizon:install
docker-compose exec ogamex-app php artisan migrate

# Access at http://localhost/horizon
```

### Logging and Analysis

#### Log Management

**Centralized Logging**:
```yaml
# Add to docker-compose.yml
services:
  ogamex-app:
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"
```

**Log Rotation**:
```bash
# Configure log rotation
sudo cat > /etc/logrotate.d/ogamex << EOF
/var/lib/docker/containers/*/*.log {
    daily
    missingok
    rotate 7
    compress
    delaycompress
    notifempty
    create 0644 root root
}
EOF
```

#### Log Analysis

**Application Logs**:
```bash
# Laravel logs
docker-compose exec ogamex-app tail -f /var/www/storage/logs/laravel.log

# Error log filter
docker-compose exec ogamex-app grep -i error /var/www/storage/logs/laravel.log

# Authentication logs
docker-compose exec ogamex-app grep -i "authentication" /var/www/storage/logs/laravel.log
```

**Web Server Logs**:
```bash
# Nginx access logs
docker-compose exec ogamex-webserver tail -f /var/log/nginx/access.log

# Error logs
docker-compose exec ogamex-webserver tail -f /var/log/nginx/error.log

# Top IPs
docker-compose exec ogamex-webserver awk '{print $1}' /var/log/nginx/access.log | sort | uniq -c | sort -nr
```

**Database Logs**:
```bash
# MariaDB error log
docker-compose exec ogamex-db tail -f /var/log/mysql/error.log

# Slow query log
docker-compose exec ogamex-db tail -f /var/log/mysql/slow.log

# General query log
docker-compose exec ogamex-db grep -v "administrator command" /var/log/mysql/general.log
```

### Performance Monitoring

#### Response Time Monitoring

**HTTP Response Times**:
```bash
# Curl timing
curl -w "@curl-format.txt" -o /dev/null -s http://localhost

# Create curl-format.txt
cat > curl-format.txt << EOF
     time_namelookup:  %{time_namelookup}\n
        time_connect:  %{time_connect}\n
     time_appconnect:  %{time_appconnect}\n
    time_pretransfer:  %{time_pretransfer}\n
       time_redirect:  %{time_redirect}\n
  time_starttransfer:  %{time_starttransfer}\n
                     ----------\n
          time_total:  %{time_total}\n
EOF
```

**Laravel Performance**:
```bash
# Laravel route timing
docker-compose exec ogamex-app php artisan route:list --path=api | grep GET

# Database query analysis
docker-compose exec ogamex-app php artisan db:show --counts
```

#### Database Performance

**Query Performance**:
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Show slow queries
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;

-- Show process list
SHOW PROCESSLIST;

-- Show table locks
SHOW OPEN TABLES WHERE In_use > 0;
```

**Index Analysis**:
```sql
-- Check indexes on users table
SHOW INDEX FROM users;

-- Check index usage
SELECT 
    TABLE_SCHEMA,
    TABLE_NAME,
    INDEX_NAME,
    CARDINALITY
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'laravel'
ORDER BY CARDINALITY DESC;
```

### Alerting System

#### Health Check Alerts

**Custom Health Check Script**:
```bash
#!/bin/bash
# health-monitor.sh

APP_URL="http://localhost"
HEALTH_URL="${APP_URL}/health"
ALERT_EMAIL="admin@example.com"

# Check application health
if ! curl -f -s "${HEALTH_URL}" > /dev/null; then
    echo "Application health check failed!" | mail -s "OGameX Alert" "${ALERT_EMAIL}"
    
    # Restart application
    docker-compose restart ogamex-app
    
    # Wait and check again
    sleep 30
    if ! curl -f -s "${HEALTH_URL}" > /dev/null; then
        echo "Application restart failed!" | mail -s "OGameX Critical Alert" "${ALERT_EMAIL}"
    fi
fi
```

**Automated Monitoring Script**:
```bash
#!/bin/bash
# monitor.sh

# Check all services
SERVICES=("ogamex-app" "ogamex-db" "ogamex-webserver" "ogamex-redis")

for service in "${SERVICES[@]}"; do
    if ! docker ps --filter "name=${service}" --filter "health=healthy" --quiet | grep -q .; then
        echo "Service ${service} is not healthy!"
        
        # Restart service
        docker-compose restart "${service}"
        
        # Log the event
        echo "$(date): Service ${service} restarted due to health check failure" >> /var/log/ogamex-health.log
    fi
done
```

#### External Monitoring

**Uptime Robot Integration**:
- Configure HTTP(s) monitor for your application URL
- Set up keyword monitoring for "healthy" response
- Configure email and SMS alerts

**Pingdom Setup**:
- Monitor application availability
- Set up performance monitoring
- Configure multiple locations

**Custom Monitoring Dashboard**:
```bash
#!/bin/bash
# dashboard.sh - Generate monitoring dashboard

echo "=== OGameX Monitoring Dashboard ==="
echo "Generated: $(date)"
echo ""

# Service status
echo "=== Service Status ==="
docker-compose ps

echo ""
# Resource usage
echo "=== Resource Usage ==="
docker stats --no-stream

echo ""
# Application health
echo "=== Application Health ==="
curl -s http://localhost/health | python3 -m json.tool

echo ""
# Database status
echo "=== Database Status ==="
docker-compose exec ogamex-db mariadb-admin status

echo ""
# Recent errors
echo "=== Recent Errors (Last 10) ==="
docker-compose exec ogamex-app tail -n 10 /var/www/storage/logs/laravel.log | grep -i error
```

### Metrics Collection

#### Key Performance Indicators

**Application Metrics**:
- Response time (p95, p99)
- Request rate (requests/second)
- Error rate (4xx, 5xx responses)
- Database query time
- Cache hit rate
- Queue processing time

**Infrastructure Metrics**:
- CPU usage per container
- Memory usage per container
- Disk I/O per container
- Network traffic
- Database connections
- Redis connections

**Business Metrics**:
- Active users
- Game sessions
- Resource production rates
- Fleet movements
- Battle statistics

#### Metrics Collection Script
```bash
#!/bin/bash
# metrics.sh - Collect and store metrics

METRICS_FILE="/var/log/ogamex-metrics.log"

# Application metrics
response_time=$(curl -w "%{time_total}" -o /dev/null -s http://localhost)
error_rate=$(docker-compose exec ogamex-app php artisan tinker --execute="
    \$errors = \App\Models\ErrorLog::where('created_at', '>=', now()->subHour())->count();
    \$total = \App\Models\RequestLog::where('created_at', '>=', now()->subHour())->count();
    echo \$total > 0 ? (\$errors / \$total) * 100 : 0;
")

# Infrastructure metrics
app_cpu=$(docker stats --no-stream --format "{{.CPUPerc}}" ogamex-app)
app_mem=$(docker stats --no-stream --format "{{.MemUsage}}" ogamex-app)

# Database metrics
db_connections=$(docker-compose exec ogamex-db mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';" | grep Threads_connected | awk '{print $2}')

# Log metrics
echo "$(date),response_time:${response_time},error_rate:${error_rate},app_cpu:${app_cpu},app_mem:${app_mem},db_connections:${db_connections}" >> "${METRICS_FILE}"
```

### Alert Configuration

#### Threshold-Based Alerts
```bash
# Set up alerts for high resource usage
# High CPU usage (>80% for 5 minutes)
*/5 * * * * /usr/local/bin/check-cpu.sh 80

# High memory usage (>85%)
*/5 * * * * /usr/local/bin/check-memory.sh 85

# Disk space warning (>80%)
*/10 * * * * /usr/local/bin/check-disk.sh 80

# Database connection limit
*/2 * * * * /usr/local/bin/check-db-connections.sh 80
```

#### Log-Based Alerts
```bash
# Alert on specific error patterns
tail -f /var/www/storage/logs/laravel.log | grep -E "(SQLSTATE|Connection refused|Fatal error)" | \
    while read line; do
        echo "$line" | mail -s "OGameX Error Alert" admin@example.com
    done
```

---

## Performance Optimization

### Docker Performance Tuning

#### Build Optimization

**Multi-Stage Builds**:
```dockerfile
# Dockerfile optimization
FROM php:8.4-fpm AS builder

# Install build dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Build PHP extensions
RUN docker-php-ext-install zip

# Production stage
FROM php:8.4-fpm

# Copy only necessary files from builder
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Install runtime dependencies only
RUN apt-get update && apt-get install -y \
    libzip4 \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Copy application
COPY . /var/www/

# Optimize permissions
RUN chown -R www-data:www-data /var/www \
    && find /var/www -type f -exec chmod 644 {} \; \
    && find /var/www -type d -exec chmod 755 {} \;
```

**Layer Caching**:
```dockerfile
# Dockerfile - Optimize layer order
FROM php:8.4-fpm

# Install system dependencies first (changes infrequently)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy dependency files (use for caching)
COPY composer.json composer.lock /var/www/
RUN cd /var/www && composer install --no-dev --optimize-autoloader

# Copy application code
COPY . /var/www/

# Set permissions
RUN chown -R www-data:www-data /var/www
```

**Build Optimization**:
```bash
# Use BuildKit for parallel builds
export DOCKER_BUILDKIT=1

# Build with cache
docker build \
    --cache-from ogamex-app:latest \
    --build-arg BUILDKIT_INLINE_CACHE=1 \
    -t ogamex-app:latest .

# Multi-platform build
docker buildx build \
    --platform linux/amd64,linux/arm64 \
    -t ogamex-app:latest \
    --load .
```

#### Runtime Optimization

**Resource Limits**:
```yaml
# docker-compose.yml - Resource constraints
services:
  ogamex-app:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '0.5'
          memory: 512M

  ogamex-db:
    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 1G
        reservations:
          cpus: '0.25'
          memory: 256M

  ogamex-webserver:
    deploy:
      resources:
        limits:
          cpus: '0.5'
          memory: 512M
        reservations:
          cpus: '0.1'
          memory: 128M
```

### Application Performance

#### Laravel Optimization

**Cache Configuration**:
```bash
# Optimize for production
docker-compose exec ogamex-app php artisan config:cache
docker-compose exec ogamex-app php artisan route:cache
docker-compose exec ogamex-app php artisan view:cache
docker-compose exec ogamex-app php artisan event:cache
docker-compose exec ogamex-app php artisan optimize

# Clear all caches
docker-compose exec ogamex-app php artisan cache:clear
docker-compose exec ogamex-app php artisan config:clear
docker-compose exec ogamex-app php artisan route:clear
docker-compose exec ogamex-app php artisan view:clear
docker-compose exec ogamex-app php artisan event:clear
docker-compose exec ogamex-app php artisan optimize:clear
```

**Autoloader Optimization**:
```bash
# Production autoloader
docker-compose exec ogamex-app composer install --no-dev --optimize-autoloader --classmap-authoritative

# Or with Docker Compose
docker-compose exec ogamex-app composer dump-autoload --optimize --classmap-authoritative
```

**Database Optimization**:
```php
// config/database.php - Connection pool optimization
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => [
        PDO::ATTR_TIMEOUT => 30,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode='STRICT_TRANS_TABLES',sql_auto_is_null=0,default_storage_engine=INNODB",
    ],
],
```

#### Redis Optimization

**Configuration**:
```conf
# redis/redis.prod.conf
# Memory optimization
maxmemory 512mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000
appendonly yes

# Performance tuning
tcp-keepalive 300
timeout 300
```

**Connection Pooling**:
```php
// config/database.php - Redis configuration
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', false),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        'persistent' => true,  // Connection pooling
    ],
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
    ],
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_CACHE_DB', 1),
    ],
],
```

### Web Server Optimization

#### Nginx Configuration

**Performance Tuning**:
```nginx
# nginx/conf.d/performance.conf
# Worker processes
worker_processes auto;
worker_connections 1024;

# Buffer sizes
client_body_buffer_size 128k;
client_max_body_size 10m;
client_header_buffer_size 1k;
large_client_header_buffers 4 4k;
output_buffers 1 32k;
postpone_output 1460;

# Timeouts
client_body_timeout 12;
client_header_timeout 12;
keepalive_timeout 15;
send_timeout 10;

# Gzip compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_proxied any;
gzip_comp_level 6;
gzip_types
    text/plain
    text/css
    text/xml
    text/javascript
    application/json
    application/javascript
    application/xml+rss
    application/atom+xml
    image/svg+xml;

# Caching
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

location ~* \.(svg|webp)$ {
    expires 1y;
    add_header Cache-Control "public";
}
```

**PHP-FPM Optimization**:
```ini
# php/local.ini - FPM optimization
[www]
user = www-data
group = www-data
listen = /run/php/php8.4-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

# Process management
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

# Performance settings
request_terminate_timeout = 300
rlimit_files = 131072
rlimit_core = 0

# Environment variables
env[HOSTNAME] = $HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp

# PHP settings
memory_limit = 256M
max_execution_time = 60
post_max_size = 32M
upload_max_filesize = 16M
max_input_vars = 3000
```

#### OPcache Optimization

**Configuration**:
```ini
# php/opcache.ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.save_comments=1
opcache.optimization_level=0x7FFEBFFF
```

**Verification**:
```bash
# Check OPcache status
docker-compose exec ogamex-app php -r "echo opcache_get_status() ? 'OPcache is enabled' : 'OPcache is disabled';"

# OPcache information
docker-compose exec ogamex-app php -r "var_dump(opcache_get_status());"
```

### Database Optimization

#### MariaDB Configuration

**Performance Tuning**:
```ini
# mysql/my.cnf
[mariadb]
# Memory settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_log_buffer_size = 16M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query cache
query_cache_type = 1
query_cache_size = 128M
query_cache_limit = 2M

# Connection settings
max_connections = 200
max_connect_errors = 10000
connect_timeout = 60
wait_timeout = 28800
interactive_timeout = 28800

# MyISAM settings
key_buffer_size = 32M
table_open_cache = 4000
sort_buffer_size = 2M
read_buffer_size = 2M
read_rnd_buffer_size = 8M
myisam_sort_buffer_size = 64M

# Thread settings
thread_cache_size = 8
thread_concurrency = 16

# Binary logging
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7
max_binlog_size = 100M
```

**Index Optimization**:
```sql
-- Analyze table performance
ANALYZE TABLE users, planets, fleets;

-- Check index usage
EXPLAIN SELECT * FROM users WHERE email = 'user@example.com';

-- Optimize table
OPTIMIZE TABLE users, planets, fleets;

-- Check table statistics
SHOW TABLE STATUS;

-- Index suggestions
SHOW INDEX FROM users;

-- Query performance
EXPLAIN FORMAT=JSON SELECT u.*, p.* FROM users u 
JOIN planets p ON u.id = p.user_id 
WHERE u.galaxy = 1 AND p.system = 1;
```

#### Query Optimization

**Laravel Query Optimization**:
```php
// Use eager loading to prevent N+1 queries
$users = User::with(['planets', 'fleets'])->get();

// Use database indexes with raw queries
DB::select('SELECT * FROM users WHERE galaxy = ? AND system = ?', [1, 1]);

// Use chunking for large datasets
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});

// Use cursor for memory efficiency
foreach (User::cursor() as $user) {
    // Process user
}
```

### Caching Strategies

#### Application-Level Caching

**Route Caching**:
```bash
# Cache frequently accessed routes
docker-compose exec ogamex-app php artisan route:cache

# Clear route cache
docker-compose exec ogamex-app php artisan route:clear
```

**Configuration Caching**:
```bash
# Cache configuration
docker-compose exec ogamex-app php artisan config:cache

# Clear configuration cache
docker-compose exec ogamex-app php artisan config:clear
```

**View Caching**:
```bash
# Cache compiled views
docker-compose exec ogamex-app php artisan view:cache

# Clear view cache
docker-compose exec ogamex-app php artisan view:clear
```

#### Database Query Caching

**Laravel Cache**:
```php
// Cache expensive queries
$users = Cache::remember('users.online', 3600, function () {
    return User::where('last_seen', '>', now()->subHour())->get();
});

// Cache with tags
Cache::tags(['users', 'planets'])->put('user_' . $userId, $user, 3600);

// Cache facade usage
$cached = Cache::get('key');
if (!$cached) {
    $data = expensiveOperation();
    Cache::put('key', $data, 3600);
}
```

**Redis as Database Cache**:
```php
// config/database.php - Cache connection
'connections' => [
    'mysql' => [
        // ... other settings
        'options' => [
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
        ],
    ],
],
```

#### Frontend Optimization

**Asset Optimization**:
```bash
# Laravel Mix optimization
docker-compose exec ogamex-app npm run production

# Vite optimization
docker-compose exec ogamex-app npm run build

# Enable Vite HMR for development
docker-compose exec ogamex-app npm run dev
```

**CDN Configuration**:
```php
// config/filesystems.php - CDN setup
'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],
],
```

### Load Balancing and Scaling

#### Horizontal Scaling

**Scale Queue Workers**:
```bash
# Scale queue workers
docker-compose up -d --scale ogamex-queue-worker=3

# Check scaled workers
docker-compose ps
```

**Scale Application Servers**:
```bash
# Scale PHP-FPM instances
docker-compose up -d --scale ogamex-app=3

# Load balancing with Nginx
# Nginx automatically distributes load between instances
```

#### Load Balancer Configuration

**Nginx Load Balancing**:
```nginx
# nginx/conf.d/upstream.conf
upstream php_backend {
    least_conn;
    server ogamex-app:9000 max_fails=3 fail_timeout=30s;
    # Add more servers for scaling
    # server ogamex-app-2:9000 max_fails=3 fail_timeout=30s;
    # server ogamex-app-3:9000 max_fails=3 fail_timeout=30s;
}

server {
    listen 80;
    server_name yourdomain.com;
    
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
        fastcgi_pass php_backend;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/public/index.php;
        
        # Health check
        location /health {
            access_log off;
            return 200 "healthy\n";
            add_header Content-Type text/plain;
        }
    }
}
```

### Performance Monitoring

#### Real-Time Monitoring

**Application Performance**:
```bash
# Response time monitoring
curl -w "@curl-format.txt" -o /dev/null -s http://localhost/api/health

# Database performance
docker-compose exec ogamex-db mysql -u root -p -e "SHOW STATUS LIKE 'Slow_queries';"

# Redis performance
docker-compose exec ogamex-redis redis-cli info stats
```

**Resource Monitoring**:
```bash
# Container resource usage
docker stats

# Disk I/O
docker stats --format "table {{.Container}}\t{{.BlockIO}}"

# Network I/O
docker stats --format "table {{.Container}}\t{{.NetIO}}"
```

#### Performance Profiling

**Laravel Debugbar** (Development):
```bash
# Install Laravel Debugbar
docker-compose exec ogamex-app composer require barryvdh/laravel-debugbar --dev

# Enable in development
docker-compose exec ogamex-app php artisan config:clear
```

**Telescope** (Development):
```bash
# Install Laravel Telescope
docker-compose exec ogamex-app composer require laravel/telescope --dev
docker-compose exec ogamex-app php artisan telescope:install
docker-compose exec ogamex-app php artisan migrate

# Access at http://localhost/telescope
```

### Performance Testing

#### Load Testing

**Apache Bench**:
```bash
# Basic load test
ab -n 1000 -c 10 http://localhost/

# Test specific endpoint
ab -n 100 -c 5 http://localhost/api/health

# Test with authentication
ab -n 100 -c 5 -H "Authorization: Bearer token" http://localhost/api/user
```

**wrk** (Advanced):
```bash
# Install wrk
sudo apt install wrk

# Load test
wrk -t12 -c400 -d30s http://localhost/

# Test with POST data
wrk -t12 -c400 -d30s -s scripts/post.lua http://localhost/api/planet
```

#### Database Performance Testing

**Query Performance**:
```sql
-- Benchmark query performance
SET profiling = 1;
SELECT * FROM users WHERE galaxy = 1 AND system = 1;
SHOW PROFILES;

-- Check query cache status
SHOW STATUS LIKE 'Qcache%';

-- Analyze slow queries
SHOW VARIABLES LIKE 'slow_query_log';
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;
```

---

## Conclusion

This comprehensive Docker deploymen guide covers all aspects of deploying, managing, and optimizing OGameX using Docker. The guide includes:

- **Complete architecture overview** with detailed service descriptions
- **One-command deploymen** using automated scripts
- **Environment-specific configurations** for development and production
- **Comprehensive service management** procedures
- **Database operations and maintenance** procedures
- **Automated backup and restore** capabilities
- **Detailed troubleshooting** section with common issues and solutions
- **Security best practices** for container and application security
- **Health monitoring and alerting** systems
- **Performance optimization** techniques and best practices

### Quick Reference Commands

#### Essential Commands
```bash
# Quick deployment
./deploy.sh production

# Service management
docker-compose up -d                    # Start all services
docker-compose down                     # Stop all services
docker-compose restart                  # Restart all services
docker-compose logs -f                  # Follow logs

# Application commands
docker-compose exec ogamex-app php artisan migrate
docker-compose exec ogamex-app composer install
docker-compose exec ogamex-app npm install

# Monitoring
docker stats                            # Resource usage
docker-compose ps                       # Service status
./backup.sh auto                        # Create backup
```

### Support and Resources

- **Documentation**: This guide serves as the master documentation
- **Scripts**: Use provided automation scripts for common tasks
- **Health Checks**: All services include built-in health monitoring
- **Logging**: Comprehensive logging for debugging and monitoring
- **Community**: Refer to project documentation and community resources

For additional support or questions about specific configurations, consult the individual component documentation or reach out to the development team.

---

**Last Updated**: November 2025  
**Version**: 1.0  
**Maintained By**: OGameX Development Team