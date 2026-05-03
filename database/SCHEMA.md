# Database Schema Documentation

## Overview

The Screeps Online database tracks private Screeps game servers, user accounts, and server ownership.

## Tables

### users
Stores user account information.

| Column | Type | Description |
|--------|------|-------------|
| id_user | INT | Primary key |
| name | VARCHAR(20) | Username (unique) |
| email | VARCHAR(100) | Email address (unique) |
| password | VARCHAR(255) | Password hash (bcrypt/argon2) |
| created_at | TIMESTAMP | Account creation time |
| updated_at | TIMESTAMP | Last update time |

**Indexes:**
- `idx_name` on name
- `idx_email` on email

### server_list
Stores server addresses.

| Column | Type | Description |
|--------|------|-------------|
| id_server | INT | Primary key |
| address | VARCHAR(100) | Server address (host:port, unique) |
| created_at | TIMESTAMP | When server was added |

**Indexes:**
- `idx_address` on address

### server_info
Stores server status and metadata.

| Column | Type | Description |
|--------|------|-------------|
| id_info | INT | Primary key |
| server_id | INT | Foreign key to server_list (unique) |
| name | VARCHAR(100) | Server display name |
| description | TEXT | Server description |
| version | VARCHAR(50) | Screeps version |
| players_current | INT | Current player count |
| online | TINYINT | Status: 0=unknown, 1=online, 2=offline |
| last_check | TIMESTAMP | Last availability check |
| availability | DECIMAL(5,2) | Uptime percentage (0-100) |
| user_id | INT | Foreign key to users (owner) |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Last update time |

**Foreign Keys:**
- `server_id` → server_list.id_server (CASCADE)
- `user_id` → users.id_user (SET NULL)

**Indexes:**
- `idx_online` on online
- `idx_last_check` on last_check
- `idx_user_id` on user_id

### server_availability
Tracks server uptime statistics.

| Column | Type | Description |
|--------|------|-------------|
| id_availability | INT | Primary key |
| server_id | INT | Foreign key to server_list (unique) |
| checks | INT | Total check attempts |
| successful | INT | Successful checks |

**Foreign Keys:**
- `server_id` → server_list.id_server (CASCADE)

**Calculation:**
- Availability % = (successful / checks) * 100

### password_resets
Stores password reset tokens.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | Foreign key to users |
| token | VARCHAR(64) | Reset token (unique) |
| created_at | TIMESTAMP | Token creation time |
| expires_at | TIMESTAMP | Token expiration time |

**Foreign Keys:**
- `user_id` → users.id_user (CASCADE)

**Indexes:**
- `idx_token` on token
- `idx_expires` on expires_at

### login_attempts
Tracks failed login attempts for rate limiting.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| username | VARCHAR(50) | Attempted username |
| ip_address | VARCHAR(45) | IP address (supports IPv6) |
| attempted_at | TIMESTAMP | Attempt timestamp |

**Indexes:**
- `idx_username_ip` on (username, ip_address)
- `idx_attempted` on attempted_at

### web_statistic (Optional/Legacy)
Stores web analytics data.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user | VARCHAR(15) | Username/identifier |
| browser | VARCHAR(50) | Browser string |
| os | VARCHAR(50) | Operating system |
| all | TEXT | Full user agent |
| date | VARCHAR(10) | Date string |
| time | VARCHAR(10) | Time string |
| ip | VARCHAR(15) | IP address |
| stime | VARCHAR(255) | Session time |
| sid | VARCHAR(255) | Session ID (unique) |

## Relationships

```
users (1) ----< (N) server_info
  |
  +--< (N) password_resets

server_list (1) ----< (1) server_info
  |
  +----< (1) server_availability
```

## Migration Order

When setting up an existing database:

1. `001_expand_password_field.sql` - Expand password column
2. `002_add_timestamps.sql` - Add timestamp columns
3. `003_add_indexes.sql` - Add performance indexes
4. `004_add_foreign_keys.sql` - Add referential integrity
5. `005_create_security_tables.sql` - Add security tables
6. `006_ensure_server_info_exists.sql` - Fix orphaned servers

## Fresh Installation

Use `schema.sql` for new installations - it includes all tables, indexes, and foreign keys.

## Data Flow

1. **Server Addition**: Insert into `server_list` → auto-create `server_info` and `server_availability`
2. **Server Scanning**: Update `server_info` (status, version, players) → update `server_availability` (increment checks) → calculate and update availability %
3. **Server Claiming**: User verifies ownership → update `server_info.user_id`
4. **User Registration**: Insert into `users` with hashed password
5. **Password Reset**: Insert into `password_resets` → email token → validate and update `users.password` → delete token
