# Docker Development Environment

This Laravel project includes comprehensive Docker configuration for local development with optimizations and additional services.

## Quick Start

### Prerequisites
- Docker Desktop installed
- Docker Compose installed

### Running the Development Environment

1. **Start all services:**
   ```bash
   docker-compose up -d
   ```

2. **Install dependencies:**
   ```bash
   docker-compose exec ogamex-app composer install
   docker-compose exec ogamex-app npm install
   ```

3. **Generate application key:**
   ```bash
   docker-compose exec ogamex-app php artisan key:generate
   ```

4. **Run migrations:**
   ```bash
   docker-compose exec ogamex-app php artisan migrate
   ```

5. **Access the application:**
   - Main application: http://localhost
   - Alternative port: http://localhost:8080
   - PhpMyAdmin: http://localhost:8081
   - MailHog: http://localhost:8025
   - MinIO Console: http://localhost:9001

## Configuration Files

### `.dockerignore`
Optimizes Docker builds by excluding unnecessary files:
- Dependencies (`node_modules`, `vendor`)
- Logs and cache files
- Git metadata
- IDE files
- Test files
- Documentation
- Temporary files

### `docker-compose.override.yml`
Development-specific overrides that add:

#### Additional Services
- **Redis**: Caching and session storage
- **MailHog**: Email testing interface
- **MinIO**: S3-compatible object storage
- **Elasticsearch**: Full-text search

#### Development Features
- **Xdebug**: PHP debugging support
- **Hot reload**: Live code changes
- **Extended ports**: Additional debugging ports
- **Development environment variables**: Debug mode enabled
- **Health checks**: Service monitoring

### Supporting Configuration

#### `docker/php/xdebug.ini`
Xdebug configuration for PHP debugging with:
- IDE key configuration
- Client host settings for Docker
- Trigger-based debugging
- Comprehensive logging

#### `docker/nginx/dev.conf`
Development-specific Nginx configuration with:
- Alternative port (8080)
- Disabled caching for development
- Detailed logging
- Enhanced error handling

#### `.env.local`
Local development environment variables:
- Debug mode enabled
- MailHog SMTP configuration
- MinIO S3 endpoint
- Redis and Elasticsearch connections
- Development-specific settings

## Development Tools Access

### Web Interfaces
| Service | URL | Credentials |
|---------|-----|-------------|
| Application | http://localhost | - |
| Application (Dev) | http://localhost:8080 | - |
| PhpMyAdmin | http://localhost:8081 | root/toor |
| MailHog | http://localhost:8025 | - |
| MinIO Console | http://localhost:9001 | minioadmin/minioadmin123 |

### API Endpoints
| Service | Port | Purpose |
|---------|------|---------|
| PHP-FPM | 9000 | Application processing |
| PHP-FPM Debug | 9001 | Xdebug port |
| MinIO API | 9000 | S3-compatible storage |
| Elasticsearch | 9200 | Search API |

### Databases
| Service | Port | Database | Credentials |
|---------|------|----------|-------------|
| MariaDB | 3306 | laravel | root/toor |
| Redis | 6379 | - | - |
| Elasticsearch | 9200 | - | - |

## Debugging with Xdebug

### Prerequisites
- PHPStorm, VS Code, or another IDE with Xdebug support
- Xdebug browser extension or client configuration

### VS Code Setup
1. Install "PHP Debug" extension
2. Add launch configuration:
   ```json
   {
       "version": "0.2.0",
       "configurations": [
           {
               "name": "Listen for Xdebug",
               "type": "php",
               "request": "launch",
               "port": 9003,
               "pathMappings": {
                   "/var/www": "${workspaceFolder}"
               }
           }
       ]
   }
   ```

### Triggering Debug Sessions
1. Start listening in your IDE
2. Access the application with `?XDEBUG_SESSION_START=VSCODE` parameter
3. Or use browser extension to start/stop sessions

## Common Commands

### Container Management
```bash
# View running containers
docker-compose ps

# View logs
docker-compose logs -f ogamex-app

# Restart specific service
docker-compose restart ogamex-app

# Execute commands
docker-compose exec ogamex-app bash
docker-compose exec ogamex-app php artisan tinker

# Scale services
docker-compose up --scale ogamex-queue-worker=3 -d
```

### Development Tasks
```bash
# Clear caches
docker-compose exec ogamex-app php artisan cache:clear
docker-compose exec ogamex-app php artisan config:clear
docker-compose exec ogamex-app php artisan route:clear
docker-compose exec ogamex-app php artisan view:clear

# Run tests
docker-compose exec ogamex-app php artisan test

# Build frontend assets
docker-compose exec ogamex-app npm run dev

# Database operations
docker-compose exec ogamex-app php artisan migrate:fresh --seed
docker-compose exec ogamex-db mysqldump -u root -p laravel > backup.sql
```

## Performance Optimizations

### Docker Build Optimizations
- `.dockerignore` excludes unnecessary files
- Multi-stage builds reduce image size
- Cached volumes for better performance
- Optimized layer ordering in Dockerfile

### Development Optimizations
- Cached volumes for live reload
- Disabled OPCache for real-time changes
- Optimized Nginx configuration
- Redis caching when needed

## Troubleshooting

### Common Issues

#### Port Already in Use
```bash
# Check port usage
lsof -i :80
# Kill process using port
kill -9 $(lsof -t -i:80)
```

#### Permission Issues
```bash
# Fix Laravel permissions
docker-compose exec ogamex-app chown -R www-data:www-data /var/www/storage
docker-compose exec ogamex-app chmod -R 775 /var/www/storage
```

#### Xdebug Not Connecting
1. Verify xdebug.ini configuration
2. Check firewall settings
3. Ensure IDE is listening on port 9003
4. Add `XDEBUG_SESSION_START=VSCODE` to request

#### Database Connection Issues
```bash
# Test database connection
docker-compose exec ogamex-app php artisan tinker
> DB::connection()->getPdo();
```

### Debugging Containers
```bash
# Access container shell
docker-compose exec ogamex-app /bin/bash

# Check container logs
docker-compose logs ogamex-app
docker-compose logs ogamex-db

# Monitor resource usage
docker stats
```

## Production vs Development

### Key Differences
| Aspect | Development | Production |
|--------|-------------|------------|
| Debug Mode | Enabled | Disabled |
| OPCache | Disabled | Enabled |
| Logs | Verbose | Minimal |
| Services | Extended set | Core services only |
| Volumes | Live reload | Optimized |

### Using Production Configuration
```bash
# Start production services only
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

This will exclude development-specific services and configurations while maintaining optimal performance for production-like environments.