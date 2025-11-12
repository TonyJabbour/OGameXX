# Docker Environment Configuration Guide

This guide explains the Docker environment configuration for OGameX, including container setup, secrets management, and deployment procedures.

## 📁 Files Created

### 1. Environment Configuration

- **`.env.docker`** - Container-specific environment variables optimized for Docker deployment
- **`docker-compose.secrets.yml`** - Docker Compose configuration with secrets integration
- **`docker/secrets/`** - Secure storage for sensitive configuration data

### 2. Scripts and Tools

- **`docker/entrypoint.sh`** - Enhanced container initialization script
- **`docker/deploy.sh`** - Deployment automation script

### 3. Documentation

- **`docker/secrets/README.md`** - Secrets management guide
- **`docker/secrets/.gitignore`** - Git ignore rules for secrets

## 🚀 Quick Start

### 1. Initial Setup

```bash
# Copy environment configuration
cp .env.docker .env

# Setup secrets (copy and modify placeholder values)
cp -r docker/secrets docker/secrets.local
# Edit docker/secrets.local/* with your actual values

# Run deployment
./docker/deploy.sh deploy
```

### 2. Service Access

After deployment, access these services:

- **Web Application**: http://localhost
- **MailHog UI**: http://localhost:8025
- **PhpMyAdmin**: http://localhost:8080

## 🔧 Configuration Details

### Environment Variables

The `.env.docker` file includes comprehensive configuration for:

#### Application Settings
```bash
APP_NAME=OGameX
APP_ENV=docker
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=UTC
APP_LOCALE=en_US.UTF-8
```

#### Database Configuration
```bash
DB_HOST=ogamex-db
DB_DATABASE=laravel
DB_USERNAME=root
# Password loaded from secrets
```

#### Redis Configuration
```bash
REDIS_HOST=ogamex-redis
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3
# Password loaded from secrets
```

#### Performance Settings
```bash
# Queue worker configuration
QUEUE_WORKER_CONCURRENCY=5
QUEUE_WORKER_TIMEOUT=60
QUEUE_WORKER_MAX_TRIES=3

# OpCache settings
OPCACHE_ENABLE=true
OPCACHE_MEMORY_CONSUMPTION=256
```

### Container Roles

The system supports three container roles:

#### 1. App Container (`CONTAINER_ROLE=app`)
- Main application server
- Runs PHP-FPM
- Handles web requests
- Manages database migrations and caching

#### 2. Scheduler Container (`CONTAINER_ROLE=scheduler`)
- Runs Laravel task scheduler
- Executes scheduled tasks every minute
- Manages background jobs

#### 3. Queue Container (`CONTAINER_ROLE=queue`)
- Processes queue jobs
- Supports multiple worker processes
- Handles background processing

## 🔐 Secrets Management

### Directory Structure

```
docker/secrets/
├── database/
│   ├── root_password      # Database root password
│   ├── app_password       # Application DB user password
│   └── database_name      # Database name
├── redis/
│   └── password           # Redis authentication
├── mail/
│   ├── smtp_username      # SMTP username
│   └── smtp_password      # SMTP password
├── external/
│   ├── discord_webhook    # Discord notifications
│   └── api_keys          # External service keys
└── monitoring/
    ├── health_token       # Health check authentication
    └── api_keys          # Monitoring service keys
```

### Usage in Docker Compose

```yaml
services:
  ogamex-db:
    environment:
      MYSQL_ROOT_PASSWORD_FILE: /run/secrets/db_root_password
    secrets:
      - db_root_password

secrets:
  db_root_password:
    file: ./docker/secrets/database/root_password
```

### Security Best Practices

1. **Never commit secrets to version control**
2. **Use environment-specific secret files**
3. **Set proper file permissions (600)**
4. **Rotate secrets regularly**
5. **Use managed secret services in production**

## 🐳 Docker Services

### Core Services

#### 1. ogamex-app
- **Image**: Custom built PHP-FPM
- **Role**: Main application server
- **Ports**: None (internal only)
- **Volumes**: Project files, PHP configuration
- **Dependencies**: Database, Redis

#### 2. ogamex-webserver
- **Image**: nginx:alpine
- **Role**: Web server and reverse proxy
- **Ports**: 80, 443
- **Dependencies**: Application container

#### 3. ogamex-db
- **Image**: mariadb:11.3.2-jammy
- **Role**: Primary database
- **Ports**: 3306 (development only)
- **Volumes**: Database data, configuration

#### 4. ogamex-redis (New)
- **Image**: redis:7-alpine
- **Role**: Cache and session storage
- **Ports**: 6379
- **Dependencies**: None

### Supporting Services

#### 5. ogamex-scheduler
- **Role**: Task scheduler
- **Schedule**: Runs every minute
- **Dependencies**: Application container

#### 6. ogamex-queue-worker
- **Role**: Background job processor
- **Configuration**: Concurrency, timeout settings
- **Dependencies**: Application container, Redis

#### 7. ogamex-mailhog
- **Role**: Email testing (development)
- **Ports**: 1025 (SMTP), 8025 (Web UI)
- **Purpose**: Email debugging and testing

#### 8. ogamex-phpmyadmin
- **Role**: Database administration
- **Port**: 8080
- **Dependencies**: Database container

## 📊 Enhanced Entrypoint Script

The updated `docker/entrypoint.sh` provides:

### Features

1. **Improved Error Handling**
   - Strict error mode (`set -euo pipefail`)
   - Comprehensive error trapping
   - Detailed logging with timestamps

2. **Environment Setup**
   - Timezone and locale configuration
   - Secret file loading from Docker secrets
   - Environment variable validation

3. **Redis Integration**
   - Connection testing with retry logic
   - Database connectivity verification
   - Authentication handling

4. **Database Connectivity**
   - Connection testing with retry logic
   - Graceful failure handling
   - Health check integration

5. **Role-Based Execution**
   - App container: Full application setup
   - Scheduler: Task scheduling
   - Queue: Job processing with configuration

### Configuration Options

```bash
# Timezone settings
TZ=UTC
APP_TIMEZONE=UTC
APP_LOCALE=en_US.UTF-8

# Redis configuration
REDIS_HOST=ogamex-redis
REDIS_PORT=6379
REDIS_DB=0
REDIS_REQUIRE_AUTH=true

# Queue worker settings
QUEUE_WORKER_CONCURRENCY=5
QUEUE_WORKER_TIMEOUT=60
QUEUE_WORKER_MAX_TRIES=3

# Health check settings
HEALTH_CHECK_ENABLED=true
HEALTH_CHECK_ENDPOINT=/health
```

## 🚀 Deployment Options

### Development Deployment

```bash
# Quick development setup
./docker/deploy.sh deploy

# View logs
./docker/deploy.sh logs

# Check status
./docker/deploy.sh status
```

### Production Deployment

```bash
# Production environment
export APP_ENV=production
export APP_DEBUG=false

# Use production secrets
export SECRET_PATH=/secure/secrets

# Deploy with production settings
./docker/deploy.sh deploy
```

### Custom Deployment

```bash
# Use specific compose file
docker-compose -f docker-compose.secrets.yml --env-file .env.docker up -d

# Manual service management
docker-compose -f docker-compose.secrets.yml up --build ogamex-app
docker-compose -f docker-compose.secrets.yml up -d ogamex-scheduler
```

## 📈 Monitoring and Health Checks

### Health Check Endpoint

Enable health monitoring:

```bash
HEALTH_CHECK_ENABLED=true
HEALTH_CHECK_ENDPOINT=/health
HEALTH_CHECK_TOKEN=your_secret_token
```

### Container Health Checks

```yaml
healthcheck:
  test: ["CMD-SHELL", "curl -f http://localhost:9000 || [ $? -eq 56 ]"]
  interval: 10s
  timeout: 6s
  retries: 60
```

### Logging Configuration

```yaml
# Docker logging
LOG_DOCKER_ENABLED=true
LOG_DOCKER_PATH=/var/www/storage/logs/docker
LOG_MAX_FILES=7
LOG_LEVEL=debug
```

## 🔄 Maintenance Operations

### Database Operations

```bash
# Run migrations
./docker/deploy.sh migrate

# Access database directly
docker exec -it ogamex-db mysql -u root -p

# Backup database
docker exec ogamex-db mysqldump -u root -p laravel > backup.sql
```

### Cache Management

```bash
# Clear application cache
docker exec ogamex-app php artisan cache:clear

# Regenerate caches (production)
docker exec ogamex-app php artisan config:cache
docker exec ogamex-app php artisan route:cache
```

### Redis Management

```bash
# Access Redis CLI
docker exec -it ogamex-redis redis-cli

# Clear Redis cache
docker exec ogamex-redis redis-cli FLUSHALL
```

## 🛠 Troubleshooting

### Common Issues

1. **Container won't start**
   ```bash
   # Check logs
   ./docker/deploy.sh logs
   
   # Verify environment variables
   docker-compose config
   ```

2. **Database connection fails**
   ```bash
   # Check database status
   docker exec ogamex-db mysqladmin ping
   
   # Verify credentials
   cat docker/secrets/database/root_password
   ```

3. **Redis connection fails**
   ```bash
   # Check Redis status
   docker exec ogamex-redis redis-cli ping
   
   # Verify Redis password
   cat docker/secrets/redis/password
   ```

### Debug Mode

Enable debug logging:

```bash
export APP_DEBUG=true
export LOG_LEVEL=debug
./docker/deploy.sh deploy
```

## 🔧 Customization

### Adding New Secrets

1. Create secret file in appropriate directory
2. Update docker-compose.yml with secret reference
3. Add environment variable mapping
4. Update application configuration

### Adding New Services

1. Add service definition to docker-compose.secrets.yml
2. Configure dependencies and networking
3. Update deployment script if needed
4. Add health checks

### Environment Variables

Add custom environment variables in `.env.docker`:

```bash
# Custom application settings
CUSTOM_FEATURE_ENABLED=true
CUSTOM_API_ENDPOINT=https://api.example.com
CUSTOM_TIMEOUT=30
```

## 📚 Additional Resources

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Docker Integration](https://laravel.com/docs/9.x/deployment)
- [Redis Docker Documentation](https://redis.io/docs/manual/docker/)
- [MariaDB Docker Documentation](https://hub.docker.com/_/mariadb)

## 📝 Summary

The Docker environment configuration provides:

✅ **Comprehensive environment variables** for containerized deployment  
✅ **Secure secrets management** with proper separation  
✅ **Enhanced error handling** and logging  
✅ **Redis integration** for caching and sessions  
✅ **Timezone and locale support** for internationalization  
✅ **Health checks and monitoring** capabilities  
✅ **Automated deployment** scripts  
✅ **Production-ready configuration** with optimization options  

This setup ensures a robust, secure, and scalable containerized environment for OGameX development and deployment.
