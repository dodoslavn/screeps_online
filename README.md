# Screeps Online - Private Server Directory

A secure, community-driven directory for Screeps private game servers. Players can discover servers, and server owners can claim and manage their listings.

## Features

- 🔍 **Server Discovery** - Browse active Screeps private servers
- 📊 **Live Stats** - Real-time player counts and availability tracking
- 🔐 **Server Claiming** - Verify ownership and manage your server listing
- 👤 **User Accounts** - Secure authentication with modern password hashing
- 🛡️ **Security** - Protected against SQL injection, XSS, CSRF attacks

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache/Nginx)
- Cron for server scanning

**No Composer or external dependencies required!**

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/dodoslavn/screeps_online.git
cd screeps_online
```

### 2. Configure Application

```bash
nano config/env.php
```

Update the configuration array with your settings:
- Database credentials (DB_HOST, DB_NAME, DB_USER, DB_PASS)
- App URL (APP_URL)
- Set APP_ENV=production and APP_DEBUG=false for production
- Set SESSION_SECURE=true if using HTTPS (recommended)

**Note:** The autoloader is already created at `vendor/autoload.php` - no Composer needed!

### 3. Set Up Database

**Option A: Fresh Installation**

```bash
mysql -u root -p < database/schema.sql
```

**Option B: Migrate Existing Database**

```bash
php database/migrate.php
```

This will:
- Expand password field for modern hashes
- Add timestamps and indexes
- Add foreign key constraints
- Create security tables

### 4. Configure Web Server

**Apache**

Point DocumentRoot to `/path/to/screeps_online/public`

```apache
<VirtualHost *:80>
    ServerName screeps.online
    DocumentRoot /path/to/screeps_online/public

    <Directory /path/to/screeps_online/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Force HTTPS (recommended)
    # RewriteEngine On
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>
```

**Nginx**

```nginx
server {
    listen 80;
    server_name screeps.online;
    root /path/to/screeps_online/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Force HTTPS (recommended)
    # return 301 https://$server_name$request_uri;
}
```

### 5. Set File Permissions

```bash
# Make storage directories writable
chmod -R 775 storage/logs storage/sessions

# Protect config file
chmod 600 config/env.php

# Make scripts executable
chmod +x backend/scan_new.sh database/migrate.php
```

### 6. Set Up Cron Job

```bash
crontab -e
```

Add:
```
* * * * * /home/dodanek/screeps_online/backend/scan_new.sh
```

Or directly with PHP:
```
* * * * * /usr/bin/php /home/dodanek/screeps_online/backend/scan_new.php >> /home/dodanek/screeps_online/storage/logs/scan.log 2>&1
```

## Security Features

### Implemented Protections

✅ **SQL Injection** - All queries use prepared statements with PDO  
✅ **XSS** - All output escaped with htmlspecialchars()  
✅ **CSRF** - Token validation on all POST requests  
✅ **Password Security** - Argon2ID/BCrypt with automatic rehashing  
✅ **Session Security** - Secure cookies, regeneration, timeout  
✅ **Rate Limiting** - Login attempts, registration, password reset  
✅ **Input Validation** - Comprehensive server-side validation  

### Migration from Old Passwords

Users with old crypt() passwords will be prompted to reset their password. Two options:

1. **Force Reset** (current implementation)
   - Users with old passwords must use password reset feature
   - Most secure option

2. **Transparent Migration** (alternative)
   - Update AuthService to migrate on login
   - See comments in `src/Services/AuthService.php`

## Architecture

```
screeps_online/
├── backend/          # Cron jobs
├── config/           # Configuration files
├── database/         # SQL migrations
├── public/           # Web root
│   ├── assets/      # CSS, images, JS
│   └── index.php    # Front controller
├── src/
│   ├── Controllers/ # Request handlers
│   ├── Models/      # Database access
│   ├── Services/    # Business logic
│   ├── Middleware/  # Request filtering
│   └── Views/       # PHP templates
├── storage/         # Logs, sessions
└── vendor/          # Composer dependencies
```

## Development

### Run Tests

```bash
composer test
```

### Code Quality

```bash
composer phpstan
```

### Enable Debug Mode

In `.env`:
```
APP_ENV=development
APP_DEBUG=true
```

**⚠️ Never enable debug mode in production!**

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions including:
- Pre-deployment checklist
- Rollback procedures
- Security hardening
- Monitoring setup

## Troubleshooting

### Servers not scanning

Check cron is running:
```bash
tail -f storage/logs/scan.log
```

### "Database connection failed"

Verify `.env` database credentials:
```bash
mysql -h DB_HOST -u DB_USER -p DB_NAME
```

### "CSRF token validation failed"

Clear browser cookies and try again. Ensure sessions are working:
```bash
ls -la storage/sessions/
```

### Permission denied errors

Ensure storage directories are writable:
```bash
chmod -R 775 storage/
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Security

To report a security vulnerability, email: screeps@fordo.sk

## License

GPL-3.0 License - see LICENSE file for details

## Credits

- **Creator**: Dodoslav Novák
- **Email**: screeps@fordo.sk
- **Original Version**: 2019
- **Security Refactor**: 2026

## Links

- **Screeps**: https://screeps.com/
- **Steam**: https://store.steampowered.com/app/464350/Screeps/
- **Community**: https://chat.screeps.com/
