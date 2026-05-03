#!/usr/bin/env php
<?php

/**
 * Server Scanner - Cron Job
 *
 * Polls all registered Screeps servers to check their availability
 * Run via cron: * * * * * /usr/bin/php /path/to/scan.php
 */

require __DIR__ . '/../vendor/autoload.php';

use ScreepsOnline\Models\Server;
use ScreepsOnline\Services\ServerScanner;

// Configure timezone
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

// Start scanning
echo "[" . date('Y-m-d H:i:s') . "] Starting server scan...\n";
logMessage('Starting server scan', 'INFO');

try {
    $serverModel = new Server();
    $scanner = new ServerScanner(10); // 10 second timeout

    // Get all servers
    $servers = $serverModel->getAll();
    $totalServers = count($servers);
    $successCount = 0;
    $failureCount = 0;

    echo "Found {$totalServers} servers to scan\n";

    foreach ($servers as $server) {
        $address = $server['address'];
        $serverId = $server['id_server'];

        echo "Scanning {$address}... ";

        $result = $scanner->scanServer($serverId, $address);

        if ($result['success']) {
            echo "OK - Version: {$result['version']}, Players: {$result['players']}\n";
            $successCount++;
        } else {
            echo "FAILED - {$result['error']}\n";
            $failureCount++;
        }
    }

    echo "\nScan complete: {$successCount} online, {$failureCount} offline\n";
    logMessage("Scan complete: {$successCount} online, {$failureCount} offline", 'INFO');

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    logMessage('Scanner error: ' . $e->getMessage(), 'ERROR', [
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
}

echo "[" . date('Y-m-d H:i:s') . "] Scan finished\n";
