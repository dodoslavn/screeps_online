-- Ensure server_info records exist for all servers
-- This prevents issues with the scanner

INSERT INTO server_info (server_id, online, last_check)
SELECT id_server, 0, NULL
FROM server_list
WHERE id_server NOT IN (SELECT server_id FROM server_info);
