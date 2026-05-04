#!/usr/bin/env php
<?php
/**
 * Database Migration Script
 *
 * Migrates existing database to new secure schema
 * Run this ONCE to upgrade from old version
 */

require __DIR__ . '/../lib/database.php';

echo "Screeps Online Database Migration\n";
echo "==================================\n\n";

try {
    $pdo = db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "[1/6] Checking current database structure...\n";

    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        die("Error: Users table not found. Please run schema.sql first.\n");
    }

    echo "[2/6] Expanding password column...\n";

    // Check current password column size
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'password'");
    $column = $stmt->fetch();

    if ($column && strpos($column['Type'], 'varchar(50)') !== false) {
        echo "  - Expanding password from VARCHAR(50) to VARCHAR(255)...\n";
        $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL");
        echo "  ✓ Password column expanded\n";
    } else {
        echo "  ✓ Password column already correct size\n";
    }

    echo "[3/6] Adding timestamp columns if missing...\n";

    // Check for created_at column
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    if ($stmt->rowCount() === 0) {
        echo "  - Adding created_at column...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    // Check for updated_at column
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'updated_at'");
    if ($stmt->rowCount() === 0) {
        echo "  - Adding updated_at column...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    echo "  ✓ Timestamp columns added\n";

    echo "[4/6] Adding indexes for performance...\n";

    // Add email index if not exists
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'idx_email'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD INDEX idx_email (email)");
        echo "  ✓ Email index added\n";
    }

    echo "[5/6] Updating server_list structure...\n";

    // Check for user_id column in server_list
    $stmt = $pdo->query("SHOW COLUMNS FROM server_list LIKE 'user_id'");
    if ($stmt->rowCount() === 0) {
        echo "  - Adding user_id column...\n";
        $pdo->exec("ALTER TABLE server_list ADD COLUMN user_id INT(11) DEFAULT NULL");
        $pdo->exec("ALTER TABLE server_list ADD INDEX idx_user_id (user_id)");
    }

    // Check for created_at column in server_list
    $stmt = $pdo->query("SHOW COLUMNS FROM server_list LIKE 'created_at'");
    if ($stmt->rowCount() === 0) {
        echo "  - Adding created_at column...\n";
        $pdo->exec("ALTER TABLE server_list ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    echo "  ✓ Server list structure updated\n";

    echo "[6/6] Checking for old password hashes...\n";

    // Count users with old crypt() passwords (length < 60)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE LENGTH(password) < 60");
    $result = $stmt->fetch();
    $oldPasswordCount = $result['count'];

    if ($oldPasswordCount > 0) {
        echo "  ⚠ Warning: {$oldPasswordCount} user(s) have old password hashes\n";
        echo "  These users will need to reset their passwords\n";
        echo "  Old passwords use weak crypt() and cannot be migrated\n";
    } else {
        echo "  ✓ All passwords use secure hashing\n";
    }

    echo "\n";
    echo "Migration completed successfully!\n";
    echo "==================================\n";

    if ($oldPasswordCount > 0) {
        echo "\nNext steps:\n";
        echo "1. Users with old passwords must use password reset\n";
        echo "2. Consider notifying users via email\n";
        echo "3. Old passwords cannot be converted for security reasons\n";
    }

} catch (PDOException $e) {
    echo "\nError during migration: " . $e->getMessage() . "\n";
    exit(1);
}
