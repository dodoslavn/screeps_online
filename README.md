# Screeps Private Server Directory

A community-driven directory website for Screeps private servers, running at **[screeps.dodoslav.eu](https://screeps.dodoslav.eu)**

## About

This website allows Screeps players to discover and connect to private community servers. Server owners can add and manage their server listings with real-time status monitoring.

### Features

- 🔍 **Browse Active Servers** - List of all registered Screeps private servers
- 📊 **Real-time Monitoring** - Automatic server status and player count tracking
- 🔐 **Server Management** - Claim and edit your server listings
- 👤 **User Authentication** - Secure account system for server owners
- 📈 **Availability Stats** - Historical uptime and availability tracking

## Technology Stack

- **Backend**: PHP 8+ with procedural architecture
- **Database**: MariaDB with PDO prepared statements
- **Frontend**: Modern CSS with gradient design
- **Security**: CSRF protection, bcrypt passwords, XSS prevention
- **Deployment**: Jenkins CI/CD with systemd services

## Project Structure

```
screeps_online/
├── web/                    # Public web files
│   ├── index.php          # Server list homepage
│   ├── add/               # Add new server
│   ├── server/            # Server details and management
│   ├── account/           # User authentication
│   ├── about/             # About page
│   ├── all/               # Full server list
│   └── default.css        # Modern styles
├── lib/                   # Shared PHP utilities
│   ├── database.php       # PDO database connection
│   ├── auth.php          # Authentication & password hashing
│   ├── security.php      # CSRF, XSS, validation
│   └── helpers.php       # Flash messages, redirects, config
├── backend/              # Background tasks
│   ├── scan.php         # Server scanner script
│   ├── scan.sh          # Scanner wrapper
│   └── screeps-scanner.* # Systemd service & timer
├── config/              # Configuration
│   └── env.php         # Database, app, site settings
├── database/           # Database setup
│   ├── schema.sql     # Complete database schema
│   └── migrate.php    # Migration script
└── tests/             # Integration tests
```

## Security Features

This project was completely refactored in 2026 to implement modern security practices:

- ✅ **SQL Injection Prevention** - PDO prepared statements throughout
- ✅ **XSS Protection** - Output escaping on all user data
- ✅ **CSRF Protection** - Token validation on all forms
- ✅ **Secure Passwords** - bcrypt hashing with `password_hash()`
- ✅ **Session Security** - Regeneration, timeout, secure cookies
- ✅ **Input Validation** - Server address and email validation

## Installation

### Prerequisites

- PHP 8.0 or higher
- MariaDB 10.5 or higher
- Web server (Apache/Nginx)
- Systemd (for scanner service)

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/dodoslavn/screeps_online.git
   cd screeps_online
   ```

2. **Create database**
   ```bash
   mysql -u root -p
   CREATE DATABASE screeps CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'screeps'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON screeps.* TO 'screeps'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Import schema**
   ```bash
   mysql -u screeps -p screeps < database/schema.sql
   ```

4. **Configure application**
   ```bash
   cp config/env.example.php config/env.php
   nano config/env.php
   # Edit database credentials and site settings
   ```

5. **Set up web server**
   
   Point document root to: `/path/to/screeps_online/web/`

   **Apache example** (`/etc/apache2/sites-available/screeps.conf`):
   ```apache
   <VirtualHost *:80>
       ServerName screeps.dodoslav.eu
       DocumentRoot /path/to/screeps_online/web
       
       <Directory /path/to/screeps_online/web>
           Options -Indexes +FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

6. **Install systemd scanner service**
   ```bash
   sudo cp backend/screeps-scanner.service /etc/systemd/system/
   sudo cp backend/screeps-scanner.timer /etc/systemd/system/
   
   # Edit service file to set correct paths
   sudo nano /etc/systemd/system/screeps-scanner.service
   
   # Enable and start
   sudo systemctl daemon-reload
   sudo systemctl enable screeps-scanner.timer
   sudo systemctl start screeps-scanner.timer
   ```

## Database Schema

### Main Tables

**`server_list`** - Registered Screeps servers
- `id_server` (INT) - Primary key
- `address` (VARCHAR) - Server address (domain:port or IP:port)
- `user_id` (INT) - Owner's user ID (nullable)

**`server_info`** - Server status and details
- `id_info` (INT) - Primary key
- `id_server` (INT) - Foreign key to server_list
- `name` (VARCHAR) - Server name
- `users` (INT) - Current player count
- `version` (VARCHAR) - Screeps version
- `lastTick` (DATETIME) - Last tick timestamp
- `status` (VARCHAR) - Server status
- `lastcheck` (DATETIME) - Last scanner check

**`server_availability`** - Uptime statistics
- `id_availability` (INT) - Primary key
- `server_id` (INT) - Foreign key to server_list
- `checks` (INT) - Total checks performed
- `successful` (INT) - Successful checks count

**`users`** - User accounts
- `id_user` (INT) - Primary key
- `name` (VARCHAR) - Username
- `email` (VARCHAR) - Email address
- `password` (VARCHAR) - Bcrypt password hash
- `created_at` (DATETIME) - Account creation timestamp
- `updated_at` (DATETIME) - Last update timestamp

## Background Scanner

The server scanner runs every minute via systemd timer to check all registered servers:

**What it does:**
- Queries each server's HTTP API at `http://server:port/api/version`
- Updates `server_info` table with current status and player count
- Tracks availability statistics in `server_availability`
- Runs as a hardened systemd service

**Manual execution:**
```bash
# Run scanner once
/path/to/screeps_online/backend/scan.sh

# Check service status
sudo systemctl status screeps-scanner.timer
sudo systemctl status screeps-scanner.service

# View logs
sudo journalctl -u screeps-scanner.service
```

## Development

### Running Tests

```bash
cd tests/
php integration-test.php
```

### Code Style

This project uses procedural PHP for simplicity:
- No MVC framework
- No Composer dependencies
- File-based routing via `/web/` directory structure
- Shared utilities in `/lib/` directory
- Pure PHP with zero external dependencies

### Contributing

1. Fork the repository
2. Create a feature branch
3. Follow existing code style (procedural PHP)
4. Ensure all security measures are maintained
5. Test thoroughly
6. Submit a pull request

## Configuration

### Environment Variables

Edit `config/env.php`:

```php
return [
    // Database Configuration
    'database' => [
        'host' => 'localhost',
        'name' => 'screeps',
        'user' => 'screeps',
        'password' => 'your_password',
    ],

    // Application Settings
    'app' => [
        'url' => 'https://screeps.dodoslav.eu',
        'name' => 'Screeps.dodoslav.eu',
        'timezone' => 'Europe/Bratislava',
    ],

    // Site Information
    'site' => [
        'email' => 'screeps@dodoslav.eu',
        'year' => '2019',
    ],

    // Session Configuration
    'session' => [
        'secure' => true,      // HTTPS only
        'httponly' => true,    // No JavaScript access
        'samesite' => 'Lax',   // CSRF protection
        'lifetime' => 7200,    // 2 hours
    ],
];
```

## Deployment

### Jenkins CI/CD

The project includes Jenkins deployment configuration. See existing Jenkins jobs for:
- Automatic deployment on git push
- Configuration management
- Service restart

### Manual Deployment

```bash
# Pull latest code
git pull origin main

# Run migrations if needed
php database/migrate.php

# Restart scanner service
sudo systemctl restart screeps-scanner.service

# Clear any PHP opcache if enabled
sudo systemctl reload php-fpm  # or apache2
```

## License

This project is open source. Screeps game is owned by Screeps LLC.

## Links

- **Live Site**: https://screeps.dodoslav.eu
- **Screeps Official**: https://screeps.com/
- **Screeps Steam**: https://store.steampowered.com/app/464350/Screeps/
- **Community Slack**: https://chat.screeps.com/

## Credits

Originally created in 2019, completely refactored in 2026 with modern security practices.

**Maintainer**: dodoslavn (screeps@dodoslav.eu)
