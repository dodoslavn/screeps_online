<?php

namespace ScreepsOnline\Models;

/**
 * Server Model
 *
 * Handles server_list table operations
 */
class Server
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all servers with their info
     *
     * @param int|null $daysBack Limit to servers checked within X days
     * @return array
     */
    public function getAll(?int $daysBack = null): array
    {
        $sql = "
            SELECT
                sl.id_server,
                sl.address,
                si.name,
                si.description,
                si.version,
                si.players_current,
                si.online,
                si.last_check,
                si.availability,
                si.user_id,
                u.name as owner_name
            FROM server_list sl
            LEFT JOIN server_info si ON sl.id_server = si.server_id
            LEFT JOIN users u ON si.user_id = u.id_user
        ";

        $params = [];

        if ($daysBack !== null) {
            $sql .= " WHERE si.last_check > DATE(NOW() - INTERVAL ? DAY)";
            $params[] = $daysBack;
        }

        $sql .= " ORDER BY si.players_current DESC, si.online ASC, sl.address ASC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get server by address
     *
     * @param string $address Format: "host:port"
     * @return array|false
     */
    public function getByAddress(string $address)
    {
        $sql = "
            SELECT
                sl.id_server,
                sl.address,
                si.name,
                si.description,
                si.version,
                si.players_current,
                si.online,
                si.last_check,
                si.availability,
                si.user_id,
                u.name as owner_name,
                u.email as owner_email
            FROM server_list sl
            LEFT JOIN server_info si ON sl.id_server = si.server_id
            LEFT JOIN users u ON si.user_id = u.id_user
            WHERE sl.address = ?
            LIMIT 1
        ";

        return $this->db->fetch($sql, [$address]);
    }

    /**
     * Get server by ID
     *
     * @param int $id
     * @return array|false
     */
    public function getById(int $id)
    {
        $sql = "
            SELECT
                sl.id_server,
                sl.address,
                si.name,
                si.description,
                si.version,
                si.players_current,
                si.online,
                si.last_check,
                si.availability,
                si.user_id
            FROM server_list sl
            LEFT JOIN server_info si ON sl.id_server = si.server_id
            WHERE sl.id_server = ?
            LIMIT 1
        ";

        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Create a new server entry
     *
     * @param string $address Format: "host:port"
     * @return string Last inserted ID
     */
    public function create(string $address): string
    {
        $sql = "INSERT INTO server_list (address) VALUES (?)";
        return $this->db->insert($sql, [$address]);
    }

    /**
     * Check if server address already exists
     *
     * @param string $address
     * @return bool
     */
    public function addressExists(string $address): bool
    {
        $sql = "SELECT COUNT(*) FROM server_list WHERE address = ?";
        return (int)$this->db->fetchColumn($sql, [$address]) > 0;
    }

    /**
     * Delete a server
     *
     * @param int $serverId
     * @return int Number of affected rows
     */
    public function delete(int $serverId): int
    {
        $sql = "DELETE FROM server_list WHERE id_server = ?";
        return $this->db->execute($sql, [$serverId]);
    }
}
