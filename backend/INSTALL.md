# Backend Scanner Installation

The backend scanner monitors Screeps servers and updates their status in the database.

## Installation Options

### Option 1: Systemd (Recommended)

Modern, reliable, with better logging and control.

```bash
# Copy service files to systemd directory
# Note: You can rename to avoid conflicts (e.g., custom-screeps_scanner.service)
sudo cp backend/screeps-scanner.service /etc/systemd/system/
sudo cp backend/screeps-scanner.timer /etc/systemd/system/

# Edit the service file to update paths and user
sudo nano /etc/systemd/system/screeps-scanner.service

# Update these lines:
#   User=www-data              # Change to your web user
#   Group=www-data             # Change to your web group  
#   WorkingDirectory=/path/to/your/screeps_online/backend
#   ExecStart=/usr/bin/php /path/to/your/screeps_online/backend/scan.php

# The service now uses journald for logging (no need to create log files)
# Logs viewable with: journalctl -u screeps-scanner.service

# Reload systemd
sudo systemctl daemon-reload

# Enable and start the timer
sudo systemctl enable screeps-scanner.timer
sudo systemctl start screeps-scanner.timer

# Check status
sudo systemctl status screeps-scanner.timer
sudo systemctl list-timers screeps-scanner*
```

**Managing the service:**

```bash
# View logs
sudo journalctl -u screeps-scanner.service -f

# Check timer status
sudo systemctl status screeps-scanner.timer

# Manually trigger a scan
sudo systemctl start screeps-scanner.service

# Stop the timer
sudo systemctl stop screeps-scanner.timer

# Restart after changes
sudo systemctl daemon-reload
sudo systemctl restart screeps-scanner.timer
```

### Option 2: Cron (Legacy)

Simple but less flexible.

```bash
# Edit crontab
crontab -e

# Add this line to run every minute:
* * * * * /home/dodanek/screeps_online/backend/scan.sh

# Or run directly with PHP:
* * * * * /usr/bin/php /home/dodanek/screeps_online/backend/scan.php >> /home/dodanek/screeps_online/storage/logs/scan.log 2>&1
```

**View logs:**

```bash
tail -f storage/logs/scan.log
```

## Testing

Run the scanner manually to test:

```bash
# Direct execution
cd /home/dodanek/screeps_online/backend
php scan.php

# Via wrapper script
./scan.sh

# Via systemd (if installed)
sudo systemctl start screeps-scanner.service
```

## Troubleshooting

**Check permissions:**
```bash
# Ensure log directory is writable
chmod 775 storage/logs
chown www-data:www-data storage/logs

# Ensure scripts are executable
chmod +x backend/scan.php backend/scan.sh
```

**Test database connection:**
```bash
php -r "require 'lib/database.php'; db_connect(); echo 'OK\n';"
```

**Check PHP CLI:**
```bash
which php
php --version
```
