<?php

namespace ScreepsOnline\Models;

/**
 * ServerAvailability Model
 *
 * Handles server_availability table operations
 */
class ServerAvailability
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get availability stats for a server
     *
     * @param int $serverId
     * @return array|false
     */
    public function getStats(int $serverId)
    {
        $sql = "SELECT * FROM server_availability WHERE server_id = ? LIMIT 1";
        return $this->db->fetch($sql, [$serverId]);
    }

    /**
     * Initialize availability record for new server
     *
     * @param int $serverId
     * @return string Last inserted ID
     */
    public function initialize(int $serverId): string
    {
        $sql = "INSERT INTO server_availability (server_id, checks, successful) VALUES (?, 0, 0)";
        return $this->db->insert($sql, [$serverId]);
    }

    /**
     * Increment check counter
     *
     * @param int $serverId
     * @param bool $successful Was the check successful?
     * @return int Number of affected rows
     */
    public function incrementCheck(int $serverId, bool $successful = false): int
    {
        // Check if record exists
        $exists = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM server_availability WHERE server_id = ?",
            [$serverId]
        );

        if (!$exists) {
            $this->initialize($serverId);
        }

        if ($successful) {
            $sql = "
                UPDATE server_availability
                SET checks = checks + 1,
                    successful = successful + 1
                WHERE server_id = ?
            ";
        } else {
            $sql = "
                UPDATE server_availability
                SET checks = checks + 1
                WHERE server_id = ?
            ";
        }

        return $this->db->execute($sql, [$serverId]);
    }

    /**
     * Calculate availability percentage
     *
     * @param int $serverId
     * @return float Availability percentage (0-100)
     */
    public function calculateAvailability(int $serverId): float
    {
        $stats = $this->getStats($serverId);

        if (!$stats || $stats['checks'] == 0) {
            return 0.0;
        }

        return round(($stats['successful'] / $stats['checks']) * 100, 2);
    }

    /**
     * Reset stats for a server (useful for maintenance)
     *
     * @param int $serverId
     * @return int Number of affected rows
     */
    public function resetStats(int $serverId): int
    {
        $sql = "UPDATE server_availability SET checks = 0, successful = 0 WHERE server_id = ?";
        return $this->db->execute($sql, [$serverId]);
    }
}
