<?php

namespace ScreepsOnline\Models;

/**
 * ServerInfo Model
 *
 * Handles server_info table operations
 */
class ServerInfo
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get server info by server ID
     *
     * @param int $serverId
     * @return array|false
     */
    public function getByServerId(int $serverId)
    {
        $sql = "SELECT * FROM server_info WHERE server_id = ? LIMIT 1";
        return $this->db->fetch($sql, [$serverId]);
    }

    /**
     * Update server info (version, players, online status)
     *
     * @param int $serverId
     * @param array $data Keys: version, players_current, online
     * @return int Number of affected rows
     */
    public function updateInfo(int $serverId, array $data): int
    {
        // Check if server_info exists
        $exists = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM server_info WHERE server_id = ?",
            [$serverId]
        );

        if (!$exists) {
            // Create new server_info record
            $sql = "
                INSERT INTO server_info (server_id, version, players_current, online, last_check)
                VALUES (?, ?, ?, ?, NOW())
            ";
            return $this->db->execute($sql, [
                $serverId,
                $data['version'] ?? '',
                $data['players_current'] ?? 0,
                $data['online'] ?? 0
            ]);
        }

        // Update existing record
        $sql = "
            UPDATE server_info
            SET version = ?,
                players_current = ?,
                online = ?,
                last_check = NOW()
            WHERE server_id = ?
        ";

        return $this->db->execute($sql, [
            $data['version'] ?? '',
            $data['players_current'] ?? 0,
            $data['online'] ?? 0,
            $serverId
        ]);
    }

    /**
     * Claim server for a user (atomic operation)
     *
     * @param string $address Server address
     * @param int $userId User ID
     * @return bool True if claim successful
     */
    public function claimServer(string $address, int $userId): bool
    {
        // Atomic update - only succeeds if server is unclaimed
        $sql = "
            UPDATE server_info si
            JOIN server_list sl ON si.server_id = sl.id_server
            SET si.user_id = ?
            WHERE sl.address = ? AND si.user_id IS NULL
        ";

        return $this->db->execute($sql, [$userId, $address]) > 0;
    }

    /**
     * Update server details (name, description)
     *
     * @param int $serverId
     * @param array $data Keys: name, description
     * @return int Number of affected rows
     */
    public function updateDetails(int $serverId, array $data): int
    {
        $sql = "
            UPDATE server_info
            SET name = ?,
                description = ?
            WHERE server_id = ?
        ";

        return $this->db->execute($sql, [
            $data['name'] ?? '',
            $data['description'] ?? '',
            $serverId
        ]);
    }

    /**
     * Update availability percentage
     *
     * @param int $serverId
     * @param float $availability
     * @return int Number of affected rows
     */
    public function updateAvailability(int $serverId, float $availability): int
    {
        $sql = "UPDATE server_info SET availability = ? WHERE server_id = ?";
        return $this->db->execute($sql, [$availability, $serverId]);
    }

    /**
     * Check if user owns this server
     *
     * @param int $serverId
     * @param int $userId
     * @return bool
     */
    public function isOwnedBy(int $serverId, int $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM server_info WHERE server_id = ? AND user_id = ?";
        return (int)$this->db->fetchColumn($sql, [$serverId, $userId]) > 0;
    }

    /**
     * Check if server is claimed
     *
     * @param int $serverId
     * @return bool
     */
    public function isClaimed(int $serverId): bool
    {
        $sql = "SELECT user_id FROM server_info WHERE server_id = ?";
        $result = $this->db->fetchColumn($sql, [$serverId]);
        return $result !== null && $result !== false;
    }
}
