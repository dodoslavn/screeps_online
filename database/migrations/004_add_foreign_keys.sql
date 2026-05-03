-- Add foreign key constraints for referential integrity
-- Note: This may fail if there are orphaned records. Clean those up first if needed.

-- Server info foreign keys
ALTER TABLE server_info
    ADD CONSTRAINT IF NOT EXISTS fk_server_info_server
    FOREIGN KEY (server_id) REFERENCES server_list(id_server) ON DELETE CASCADE;

ALTER TABLE server_info
    ADD CONSTRAINT IF NOT EXISTS fk_server_info_user
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE SET NULL;

-- Server availability foreign key
ALTER TABLE server_availability
    ADD CONSTRAINT IF NOT EXISTS fk_availability_server
    FOREIGN KEY (server_id) REFERENCES server_list(id_server) ON DELETE CASCADE;
