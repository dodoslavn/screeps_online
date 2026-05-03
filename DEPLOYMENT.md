# Deployment Guide

This guide covers deploying the refactored Screeps Online application.

## Pre-Deployment Checklist

### 1. Backup Everything

```bash
# Backup database
mysqldump screepsdb > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files
tar -czf screeps_backup_$(date +%Y%m%d_%H%M%S).tar.gz /path/to/screeps_online
```

### 2. Test on Staging

- Set up identical staging environment
- Run all migrations
- Test critical user flows
- Verify cron job works
- Load test with expected traffic

### 3. Update Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Configuration

```bash
# Copy and configure .env
cp .env.example .env
nano .env

# Required settings:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
DB_HOST=localhost
DB_NAME=screepsdb
DB_USER=screepsdb
DB_PASS=your_secure_password
SESSION_SECURE=true  # If using HTTPS
```

## Deployment Steps

### Step 1: Put Site in Maintenance (Optional)

Create a maintenance page:
```bash
echo "Site is being upgraded. Back in 10 minutes!" > public/maintenance.html
```

Add to `.htaccess`:
```apache
RewriteCond %{REQUEST_URI} !^/maintenance\.html$
RewriteRule ^.*$ /maintenance.html [R=503,L]
```

### Step 2: Deploy Code

```bash
# Pull latest code
git pull origin main

# Or upload via FTP/rsync
rsync -avz --delete local/ server:/path/to/screeps_online/
```

### Step 3: Install Dependencies

```bash
cd /path/to/screeps_online
composer install --no-dev --optimize-autoloader
```

### Step 4: Run Migrations

```bash
php database/migrate.php
```

Review output for any errors. Some errors are expected if migrations were already applied.

### Step 5: Update Web Server

**Apache:**
```bash
# Update VirtualHost to point to public/ directory
sudo nano /etc/apache2/sites-available/screeps.conf
```

```apache
DocumentRoot /path/to/screeps_online/public

<Directory /path/to/screeps_online/public>
    AllowOverride All
    Require all granted
</Directory>
```

```bash
sudo apache2ctl configtest
sudo systemctl reload apache2
```

**Nginx:**
```bash
sudo nano /etc/nginx/sites-available/screeps
```

```nginx
root /path/to/screeps_online/public;
```

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### Step 6: Update Cron Job

```bash
crontab -e
```

Update to use new scanner:
```
* * * * * /usr/bin/php /path/to/screeps_online/backend/scan_new.php >> /path/to/screeps_online/storage/logs/scan.log 2>&1
```

### Step 7: Set Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /path/to/screeps_online

# Storage directories writable
chmod -R 775 storage/logs storage/sessions

# Protect .env
chmod 600 .env

# Make scripts executable
chmod +x backend/scan_new.sh database/migrate.php
```

### Step 8: Clear Maintenance Mode

Remove maintenance redirect from `.htaccess`.

### Step 9: Verify Deployment

**Critical Paths to Test:**

1. **Homepage**: https://your-domain.com
   - Server list displays
   - No PHP errors

2. **User Registration**: /account/create
   - Form displays
   - Can create account
   - Password is hashed (check DB)

3. **Login**: /account/login
   - Can log in
   - Session persists
   - Redirects to account page

4. **Add Server**: /add
   - Form displays with CSRF token
   - Can add server
   - Validation works

5. **Server Details**: /server?server=host:port
   - Page displays
   - Info is correct

6. **Cron Job**:
   ```bash
   # Run manually
   php backend/scan_new.php
   
   # Check logs
   tail -f storage/logs/scan.log
   ```

7. **Check Database**:
   ```bash
   mysql screepsdb -e "SELECT password FROM users LIMIT 1;"
   ```
   - Password should be 60+ characters (bcrypt/argon2)

### Step 10: Monitor

```bash
# Watch application logs
tail -f storage/logs/app.log

# Watch scanner logs
tail -f storage/logs/scan.log

# Watch PHP errors
tail -f storage/logs/php-errors.log

# Watch web server logs
sudo tail -f /var/log/apache2/error.log  # Apache
sudo tail -f /var/log/nginx/error.log    # Nginx
```

## Rollback Procedure

If something goes wrong:

### 1. Restore Database

```bash
mysql screepsdb < backup_TIMESTAMP.sql
```

### 2. Restore Old Code

```bash
# Restore from backup
tar -xzf screeps_backup_TIMESTAMP.tar.gz -C /

# Or git revert
git revert HEAD
```

### 3. Revert Web Server Config

```apache
# Point back to old web/ directory
DocumentRoot /path/to/screeps_online/web
```

```bash
sudo systemctl reload apache2  # or nginx
```

### 4. Revert Cron Job

```
* * * * * /path/to/screeps_online/backend/scan.sh
```

## Post-Deployment

### Security Hardening

1. **Force HTTPS**

Enable in `public/.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

2. **Update `.env`**

```bash
SESSION_SECURE=true
```

3. **Disable PHP Exposure**

In `php.ini`:
```ini
expose_php = Off
```

4. **Set Security Headers**

Already in `public/.htaccess`:
- X-Content-Type-Options
- X-Frame-Options
- X-XSS-Protection
- Content-Security-Policy

### Monitoring

1. **Set Up Log Rotation**

```bash
sudo nano /etc/logrotate.d/screeps
```

```
/path/to/screeps_online/storage/logs/*.log {
    daily
    rotate 30
    compress
    missingok
    notifempty
}
```

2. **Database Backups**

```bash
# Add to cron
0 2 * * * mysqldump screepsdb | gzip > /backup/screepsdb_$(date +\%Y\%m\%d).sql.gz
```

3. **Monitor Disk Space**

```bash
df -h /path/to/screeps_online
```

4. **Monitor Failed Logins**

```bash
mysql screepsdb -e "SELECT COUNT(*) as attempts FROM login_attempts WHERE attempted_at > NOW() - INTERVAL 1 HOUR;"
```

### Performance Optimization

1. **Enable OPcache**

In `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0  # Production only
```

2. **Database Optimization**

```sql
-- Analyze tables
ANALYZE TABLE users, server_list, server_info, server_availability;

-- Check slow queries
SHOW VARIABLES LIKE 'slow_query_log';
```

3. **Clean Old Data**

```sql
-- Clean old login attempts (older than 30 days)
DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 30 DAY;

-- Clean expired password reset tokens
DELETE FROM password_resets WHERE expires_at < NOW();
```

## Troubleshooting

### Issue: "Call to undefined function Dotenv\..."

**Solution**: Composer autoload not loaded
```bash
composer dump-autoload --optimize
```

### Issue: "CSRF token validation failed"

**Solution**: Session not working
```bash
# Check session.save_path is writable
ls -la storage/sessions/

# Or use database sessions
```

### Issue: Scanner not updating servers

**Solution**: Check cron and logs
```bash
# Test manually
php backend/scan_new.php

# Check cron is running
tail -f storage/logs/scan.log

# Verify crontab
crontab -l
```

### Issue: Database connection failed

**Solution**: Check .env and database
```bash
# Test connection
mysql -h DB_HOST -u DB_USER -p DB_NAME

# Verify .env loaded
php -r "require 'vendor/autoload.php'; var_dump(env('DB_HOST'));"
```

### Issue: 500 Internal Server Error

**Solution**: Check logs and permissions
```bash
# Check PHP error log
tail -f storage/logs/php-errors.log

# Check web server error log
sudo tail -f /var/log/apache2/error.log

# Verify permissions
ls -la public/ src/ storage/
```

## Performance Benchmarks

Expected performance (on standard VPS):

- Homepage load: < 200ms
- Server list (100 servers): < 500ms
- Database queries: < 50ms
- Scanner (per server): 1-10 seconds
- Memory usage: 20-50MB per request

## Support

If you encounter issues:

1. Check logs: `storage/logs/app.log`
2. Review this guide
3. Check GitHub issues
4. Email: screeps@fordo.sk
