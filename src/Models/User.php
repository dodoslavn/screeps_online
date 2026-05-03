<?php

namespace ScreepsOnline\Models;

/**
 * User Model
 *
 * Handles all user-related database operations
 */
class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find user by username
     *
     * @param string $username
     * @return array|false
     */
    public function findByUsername(string $username)
    {
        $sql = "SELECT * FROM users WHERE name = ? LIMIT 1";
        return $this->db->fetch($sql, [$username]);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return array|false
     */
    public function findByEmail(string $email)
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        return $this->db->fetch($sql, [$email]);
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById(int $id)
    {
        $sql = "SELECT * FROM users WHERE id_user = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Create a new user
     *
     * @param string $username
     * @param string $email
     * @param string $passwordHash
     * @return string Last inserted ID
     */
    public function create(string $username, string $email, string $passwordHash): string
    {
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        return $this->db->insert($sql, [$username, $email, $passwordHash]);
    }

    /**
     * Update user password
     *
     * @param int $userId
     * @param string $newPasswordHash
     * @return int Number of affected rows
     */
    public function updatePassword(int $userId, string $newPasswordHash): int
    {
        $sql = "UPDATE users SET password = ? WHERE id_user = ?";
        return $this->db->execute($sql, [$newPasswordHash, $userId]);
    }

    /**
     * Check if user needs to reset password (old hash format)
     *
     * @param string $passwordHash
     * @return bool
     */
    public function needsPasswordReset(string $passwordHash): bool
    {
        // Old crypt() hashes are much shorter than password_hash() hashes
        // Modern password_hash() produces strings of at least 60 characters
        return strlen($passwordHash) < 60;
    }

    /**
     * Get all servers owned by user
     *
     * @param int $userId
     * @return array
     */
    public function getOwnedServers(int $userId): array
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
                si.availability
            FROM server_info si
            JOIN server_list sl ON si.server_id = sl.id_server
            WHERE si.user_id = ?
            ORDER BY si.last_check DESC
        ";

        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Check if username already exists
     *
     * @param string $username
     * @return bool
     */
    public function usernameExists(string $username): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE name = ?";
        return (int)$this->db->fetchColumn($sql, [$username]) > 0;
    }

    /**
     * Check if email already exists
     *
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        return (int)$this->db->fetchColumn($sql, [$email]) > 0;
    }
}
