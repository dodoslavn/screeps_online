#!/usr/bin/env php
<?php

/**
 * Database Migration Runner
 *
 * Runs all SQL migration files in order
 * Usage: php migrate.php
 */

require __DIR__ . '/../vendor/autoload.php';

// Get database config
$config = require __DIR__ . '/../config/database.php';

echo "Database Migration Tool\n";
echo "=======================\n\n";

// Connect to database
try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['database'],
        $config['charset']
    );

    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        $config['options']
    );

    echo "✓ Connected to database: {$config['database']}\n\n";

} catch (PDOException $e) {
    echo "✗ Failed to connect to database: " . $e->getMessage() . "\n";
    exit(1);
}

// Get migration files
$migrationDir = __DIR__ . '/migrations';
$migrations = glob($migrationDir . '/*.sql');
sort($migrations);

if (empty($migrations)) {
    echo "No migration files found.\n";
    exit(0);
}

echo "Found " . count($migrations) . " migration files:\n";

// Run each migration
foreach ($migrations as $migration) {
    $filename = basename($migration);
    echo "\nRunning: {$filename}\n";

    try {
        $sql = file_get_contents($migration);

        // Split by semicolons to handle multiple statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
            }
        );

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }

        echo "✓ Success\n";

    } catch (PDOException $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
        echo "  (This may be normal if the migration was already applied)\n";
    }
}

echo "\n=======================\n";
echo "Migration complete!\n";
