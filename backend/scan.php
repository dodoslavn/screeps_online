#!/usr/bin/env php
<?php
/**
 * Screeps Server Scanner
 *
 * Scans all servers in the database and updates their status
 * Run via cron: * * * * * /path/to/screeps_online/backend/scan.php
 */

require __DIR__ . '/../lib/database.php';

// Configuration
$timeout = 10; // Timeout for HTTP requests in seconds

try {
    $pdo = db_connect();

    // Get all servers from database
    $stmt = $pdo->query("SELECT sl.id_server, sl.address
                         FROM server_list sl");
    $servers = $stmt->fetchAll();

    echo "[" . date('Y-m-d H:i:s') . "] Scanning " . count($servers) . " servers...\n";

    foreach ($servers as $server) {
        $serverId = $server['id_server'];
        $address = $server['address'];

        echo "  - Scanning {$address}... ";

        // Make HTTP request to server API
        $url = "http://{$address}/api/version/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Parse response
        $online = 0;
        $playersCount = 0;
        $version = null;

        if ($response && $httpCode == 200) {
            $data = json_decode($response, true);

            if ($data && isset($data['serverData'])) {
                $online = 1;

                // Extract player count
                if (isset($data['serverData']['users'])) {
                    $playersCount = count($data['serverData']['users']);
                }

                // Extract version
                if (isset($data['package']['version'])) {
                    $version = $data['package']['version'];
                }

                echo "ONLINE (Players: {$playersCount})";
            } else {
                echo "OFFLINE (Invalid response)";
            }
        } else {
            echo "OFFLINE (HTTP {$httpCode})";
        }

        echo "\n";

        // Update server_info table
        $stmt = $pdo->prepare("
            INSERT INTO server_info (server_id, online, players_current, version, last_check)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                online = VALUES(online),
                players_current = VALUES(players_current),
                version = VALUES(version),
                last_check = VALUES(last_check)
        ");
        $stmt->execute([$serverId, $online, $playersCount, $version]);

        // Update availability statistics
        $stmt = $pdo->prepare("
            INSERT INTO server_availability (server_id, checks, successful)
            VALUES (?, 1, ?)
            ON DUPLICATE KEY UPDATE
                checks = checks + 1,
                successful = successful + VALUES(successful)
        ");
        $stmt->execute([$serverId, $online]);

        // Calculate and update availability percentage
        $stmt = $pdo->prepare("
            UPDATE server_info si
            JOIN server_availability sa ON sa.server_id = si.server_id
            SET si.availability = ROUND((sa.successful / sa.checks) * 100, 2)
            WHERE si.server_id = ?
        ");
        $stmt->execute([$serverId]);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Scan complete!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
