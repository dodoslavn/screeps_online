# 🚀 Quick Start (5 Minutes)

## 1. Configure (30 seconds)

```bash
nano config/env.php
```

Change these lines:
```php
'DB_PASS' => 'your_actual_password',  // Your MySQL password
'APP_URL' => 'https://your-domain.com',
'SESSION_SECURE' => true,  // If using HTTPS
```

## 2. Database (1 minute)

```bash
mysql -u root -p < database/schema.sql
```

Or if upgrading:
```bash
php database/migrate.php
```

## 3. Web Server (2 minutes)

**Apache:** Point to `public/` directory

```apache
DocumentRoot /home/dodanek/screeps_online/public
```

**Nginx:** Point root to `public/`

```nginx
root /home/dodanek/screeps_online/public;
```

Reload: `sudo systemctl reload apache2` or `nginx`

## 4. Permissions (30 seconds)

```bash
chmod -R 775 storage/logs storage/sessions
chmod 600 config/env.php
```

## 5. Cron (1 minute)

```bash
crontab -e
```

Add:
```
* * * * * /usr/bin/php /home/dodanek/screeps_online/backend/scan_new.php >> /home/dodanek/screeps_online/storage/logs/scan.log 2>&1
```

## ✅ Done!

Visit: `https://your-domain.com`

You should see the server list!

---

## Troubleshooting

**Can't connect to database?**
```bash
mysql -h localhost -u screepsdb -p screepsdb
```
If this fails, check `config/env.php`

**500 error?**
```bash
tail -f storage/logs/app.log
```

**Cron not working?**
```bash
php backend/scan_new.php  # Test manually
tail -f storage/logs/scan.log
```

---

## No Composer Required!

This application has **ZERO external dependencies**.

Everything runs on pure PHP 8.0+ with no packages to install!

---

See `INSTALLATION.md` for detailed instructions.
