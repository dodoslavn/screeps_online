-- Add indexes for better query performance

-- Users table indexes
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_name (name);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_email (email);

-- Server list indexes
ALTER TABLE server_list ADD INDEX IF NOT EXISTS idx_address (address);

-- Server info indexes
ALTER TABLE server_info ADD INDEX IF NOT EXISTS idx_online (online);
ALTER TABLE server_info ADD INDEX IF NOT EXISTS idx_last_check (last_check);
ALTER TABLE server_info ADD INDEX IF NOT EXISTS idx_user_id (user_id);
