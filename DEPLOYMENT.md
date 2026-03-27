# B360 Deployment Guide

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Setup](#server-setup)
3. [First Deployment](#first-deployment)
4. [Subsequent Deployments](#subsequent-deployments)
5. [Environment Variables](#environment-variables)
6. [Nginx Configuration](#nginx-configuration)
7. [Supervisor Configuration](#supervisor-configuration)
8. [Cron Setup](#cron-setup)
9. [Backup Strategy](#backup-strategy)
10. [Monitoring](#monitoring)
11. [Troubleshooting](#troubleshooting)

---

## Prerequisites

| Software    | Version  | Purpose                          |
|-------------|----------|----------------------------------|
| PHP         | 8.2+     | Application runtime              |
| MySQL       | 8.0+     | Database                         |
| Node.js     | 20+      | Frontend asset compilation       |
| Composer    | 2.x      | PHP dependency management        |
| Redis       | 7.x      | Cache, sessions, queues          |
| Nginx       | 1.24+    | Web server                       |
| Supervisor  | 4.x      | Process management (queue/cron)  |
| Git         | 2.x      | Version control                  |

### Required PHP Extensions

```
mbstring, xml, ctype, iconv, intl, pdo_mysql, zip, gd, bcmath, redis, curl, openssl, fileinfo
```

---

## Server Setup

### Ubuntu 22.04 / 24.04

```bash
# System packages
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server redis-server supervisor git unzip curl

# PHP 8.2 via Ondrej PPA
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-redis php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-fileinfo

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20 via NodeSource
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Create application user and directory
sudo mkdir -p /var/www/b360
sudo chown www-data:www-data /var/www/b360
```

### PHP-FPM tuning (`/etc/php/8.2/fpm/pool.d/www.conf`)

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### MySQL configuration

```sql
CREATE DATABASE b360 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'b360'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON b360.* TO 'b360'@'localhost';
FLUSH PRIVILEGES;
```

### SSL with Certbot

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

---

## First Deployment

```bash
cd /var/www
sudo -u www-data git clone git@github.com:YOUR_ORG/b360.git b360
cd b360

# Install dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci
sudo -u www-data npm run build

# Environment configuration
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
# Edit .env with production values (see Environment Variables section)
sudo -u www-data nano .env

# Storage permissions
sudo -u www-data mkdir -p storage/logs storage/framework/{cache,sessions,views}
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Database setup
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --force

# Cache optimization
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Storage link
sudo -u www-data php artisan storage:link
```

---

## Subsequent Deployments

### Option A: Automated (recommended)

Push to `main` branch triggers the GitHub Actions deploy workflow.

### Option B: Manual deploy script

```bash
cd /var/www/b360
sudo -u www-data ./deploy.sh
```

### Deploy script options

```bash
./deploy.sh              # Full deployment
./deploy.sh --skip-npm   # Skip NPM install and asset build
./deploy.sh --no-migrate # Skip database migrations
```

---

## Environment Variables

### Required Variables

| Variable               | Description                            | Example                    |
|------------------------|----------------------------------------|----------------------------|
| `APP_NAME`             | Application name                       | `B360`                     |
| `APP_ENV`              | Environment                            | `production`               |
| `APP_KEY`              | Encryption key (auto-generated)        | `base64:...`               |
| `APP_DEBUG`            | Debug mode (always `false` in prod)    | `false`                    |
| `APP_URL`              | Full application URL                   | `https://yourdomain.com`   |
| `DB_HOST`              | Database host                          | `127.0.0.1`               |
| `DB_PORT`              | Database port                          | `3306`                     |
| `DB_DATABASE`          | Database name                          | `b360`                     |
| `DB_USERNAME`          | Database user                          | `b360`                     |
| `DB_PASSWORD`          | Database password                      | `(strong password)`        |
| `REDIS_HOST`           | Redis host                             | `127.0.0.1`               |
| `REDIS_PORT`           | Redis port                             | `6379`                     |
| `REDIS_PASSWORD`       | Redis password                         | `null`                     |
| `CACHE_STORE`          | Cache driver                           | `redis`                    |
| `SESSION_DRIVER`       | Session driver                         | `redis`                    |
| `QUEUE_CONNECTION`     | Queue driver                           | `redis`                    |
| `MAIL_MAILER`          | Mail driver                            | `smtp`                     |
| `MAIL_HOST`            | SMTP host                              | `smtp.example.com`         |
| `MAIL_PORT`            | SMTP port                              | `465`                      |
| `MAIL_USERNAME`        | SMTP username                          | `user@example.com`         |
| `MAIL_PASSWORD`        | SMTP password                          | `(password)`               |
| `MAIL_ENCRYPTION`      | SMTP encryption                        | `ssl`                      |
| `MAIL_FROM_ADDRESS`    | Sender email                           | `noreply@yourdomain.com`   |

### Instance Configuration

| Variable                | Description                           | Example      |
|-------------------------|---------------------------------------|--------------|
| `APP_INSTALLED`         | Whether the app is installed          | `true`       |
| `INSTANCE_MODE`         | Single or multi-instance              | `single`     |
| `INSTANCE_RESOLUTION`   | How instances are resolved            | `subdomain`  |
| `INSTANCE_DB_STRATEGY`  | Database strategy for instances       | `shared`     |

### GitHub Actions Secrets (for CI/CD)

| Secret               | Description                                  |
|----------------------|----------------------------------------------|
| `DEPLOY_HOST`        | Server IP or hostname                        |
| `DEPLOY_USER`        | SSH username                                 |
| `DEPLOY_KEY`         | SSH private key (ed25519 recommended)        |
| `DEPLOY_PORT`        | SSH port (default 22)                        |
| `DEPLOY_PATH`        | Absolute path to project on server           |
| `MAINTENANCE_SECRET` | Secret token to bypass maintenance mode      |

---

## Nginx Configuration

Copy the provided config and adapt it:

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/b360
sudo ln -s /etc/nginx/sites-available/b360 /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default  # Remove default vhost
sudo nginx -t && sudo systemctl reload nginx
```

Edit `/etc/nginx/sites-available/b360`:
- Replace `example.com` with your domain
- Replace `/var/www/b360` with your actual deploy path
- Adjust `client_max_body_size` if needed

---

## Supervisor Configuration

```bash
sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/b360.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

Verify workers are running:

```bash
sudo supervisorctl status
```

Expected output:

```
b360-worker:b360-worker_00   RUNNING   pid 12345, uptime 0:05:00
b360-worker:b360-worker_01   RUNNING   pid 12346, uptime 0:05:00
b360-scheduler:b360-scheduler RUNNING   pid 12347, uptime 0:05:00
```

---

## Cron Setup

If you prefer cron over the Supervisor scheduler:

```bash
sudo crontab -u www-data -e
```

Add:

```
* * * * * cd /var/www/b360 && php artisan schedule:run >> /dev/null 2>&1
```

Note: If using the Supervisor `b360-scheduler` program, you do not need this cron entry.

---

## Backup Strategy

### Automated Database Backups

Create `/etc/cron.d/b360-backup`:

```cron
# Daily database backup at 2:00 AM
0 2 * * * www-data mysqldump -u b360 -p'DB_PASSWORD' b360 | gzip > /var/www/b360/storage/backups/db-$(date +\%Y\%m\%d-\%H\%M).sql.gz

# Clean backups older than 30 days
0 3 * * * www-data find /var/www/b360/storage/backups -name "db-*.sql.gz" -mtime +30 -delete
```

### File Backups

```bash
# Weekly full backup of storage and .env
0 3 * * 0 www-data tar -czf /var/backups/b360-files-$(date +\%Y\%m\%d).tar.gz \
    /var/www/b360/.env \
    /var/www/b360/storage/app
```

### Backup directory setup

```bash
sudo -u www-data mkdir -p /var/www/b360/storage/backups
```

---

## Monitoring

### Health Check Endpoint

The application provides two health check endpoints:

- `GET /_core/health` -- Basic check (returns `{"ok": true}`)
- `GET /health` -- Detailed check (returns database, cache, disk, queue status)

#### Example response (`/health`):

```json
{
    "status": "healthy",
    "checks": {
        "database": "ok",
        "disk": "ok",
        "cache": "ok",
        "queue": "ok"
    },
    "timestamp": "2026-03-16T12:00:00+00:00"
}
```

HTTP 200 = healthy, HTTP 503 = degraded.

### External Monitoring

Use the health endpoint with tools like:
- UptimeRobot, Better Uptime, or Pingdom
- Custom script: `curl -sf https://yourdomain.com/health || alert`

### Log Rotation

Laravel logs are in `storage/logs/`. Configure logrotate:

```bash
# /etc/logrotate.d/b360
/var/www/b360/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0664 www-data www-data
}
```

### Sentry Integration

If configured (via `SENTRY_LARAVEL_DSN` in `.env`), all unhandled exceptions are reported to Sentry automatically.

---

## Troubleshooting

### 500 Error after deployment

```bash
cd /var/www/b360
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
# Check permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
# Check logs
tail -50 storage/logs/laravel.log
```

### Queue jobs not processing

```bash
sudo supervisorctl status
# If workers are stopped:
sudo supervisorctl restart b360-worker:*
# Check worker logs:
tail -50 storage/logs/worker.log
```

### "Class not found" errors

```bash
composer dump-autoload --optimize
php artisan config:cache
```

### Database connection refused

```bash
# Verify MySQL is running
sudo systemctl status mysql
# Test connection
mysql -u b360 -p -e "SELECT 1;"
# Check .env DB_* values
```

### Redis connection refused

```bash
sudo systemctl status redis-server
redis-cli ping  # Should return PONG
```

### Maintenance mode stuck

```bash
# Force exit maintenance mode
php artisan up
# If that fails, remove the file manually:
rm storage/framework/down
```

### Asset 404 errors (CSS/JS not loading)

```bash
npm run build
php artisan storage:link
# Check Nginx root points to /public
```

### Permission denied on storage

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
# If using SELinux:
sudo chcon -R -t httpd_sys_rw_content_t storage bootstrap/cache
```
