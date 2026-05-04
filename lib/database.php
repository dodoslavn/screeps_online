<?php
/**
 * Database Connection Utility
 *
 * Provides a secure PDO database connection
 */

/**
 * Get database connection
 *
 * @return PDO
 */
function db_connect() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $config = require __DIR__ . '/../config/env.php';

    $host = $config['database']['host'] ?? 'localhost';
    $dbname = $config['database']['name'] ?? '';
    $user = $config['database']['user'] ?? '';
    $password = $config['database']['password'] ?? '';

    try {
        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        die('Database connection failed');
    }

    return $pdo;
}
