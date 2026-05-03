# Quick Installation Guide

## Zero Dependencies - No Composer Required!

This application has **NO external dependencies**. Everything runs on pure PHP 8.0+.

## Step-by-Step Setup

### 1. Upload Files

Upload all files to your server (e.g., `/home/dodanek/screeps_online/`)

### 2. Configure Application

Edit `config/env.php` with your settings:

```php
return [
    // Database credentials
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'screepsdb',
    'DB_USER' => 'screepsdb',
    'DB_PASS' => 'your_secure_password',
    
    // Application settings
    'APP_URL' => 'https://your-domain.com',
    'SESSION_SECURE' => true,  // If using HTTPS
];
```

### 3. Create Database

Run the SQL schema:

```bash
mysql -u root -p < database/schema.sql
```

Or if upgrading from old version:

```bash
php database/migrate.php
```

### 4. Configure Web Server

**Apache:**

Point DocumentRoot to `public/` directory:

```apache
DocumentRoot /home/dodanek/screeps_online/public

<Directory /home/dodanek/screeps_online/public>
    AllowOverride All
    Require all granted
</Directory>
```

Reload Apache:
```bash
sudo systemctl reload apache2
```

**Nginx:**

```nginx
root /home/dodanek/screeps_online/public;
index index.php;

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

Reload Nginx:
```bash
sudo systemctl reload nginx
```

### 5. Set Permissions

```bash
chmod -R 775 storage/logs storage/sessions
chmod 600 config/env.php
chmod +x backend/scan_new.sh
```

### 6. Set Up Cron

```bash
crontab -e
```

Add:
```
* * * * * /usr/bin/php /home/dodanek/screeps_online/backend/scan_new.php >> /home/dodanek/screeps_online/storage/logs/scan.log 2>&1
```

### 7. Test Installation

Visit your site: `https://your-domain.com`

- Homepage should show server list
- Try registering a user account
- Try adding a server
- Check logs: `tail -f storage/logs/app.log`

## What's Included

✅ **Zero Dependencies** - Pure PHP, no Composer needed  
✅ **Custom Autoloader** - PSR-4 compatible class loading  
✅ **Secure by Default** - All vulnerabilities fixed  
✅ **Production Ready** - Tested and documented  

## File Structure

```
screeps_online/
├── config/
│   └── env.php          ← Configure here
├── public/
│   └── index.php        ← Point web server here
├── src/                 ← Application code
├── database/            ← SQL migrations
├── backend/             ← Cron scanner
├── storage/             ← Logs & sessions
└── vendor/
    └── autoload.php     ← Pure PHP autoloader (no Composer!)
```

## Troubleshooting

**"Class not found"**
- Verify `vendor/autoload.php` exists
- Check file permissions: `ls -la vendor/`

**"Database connection failed"**
- Check credentials in `config/env.php`
- Test: `mysql -h localhost -u screepsdb -p screepsdb`

**"Session errors"**
- Make `storage/sessions/` writable: `chmod 775 storage/sessions`

**"Scanner not working"**
- Test manually: `php backend/scan_new.php`
- Check cron: `crontab -l`
- View logs: `tail -f storage/logs/scan.log`

## Security Notes

1. **Protect config/env.php** - Contains database password
2. **Use HTTPS** - Set `SESSION_SECURE=true` in config
3. **Keep PHP updated** - Requires PHP 8.0+
4. **Review logs** - Monitor `storage/logs/app.log` for issues

## Need Help?

- Check `README.md` for full documentation
- See `DEPLOYMENT.md` for advanced deployment
- See `SECURITY.md` for security details
- Email: screeps@fordo.sk
