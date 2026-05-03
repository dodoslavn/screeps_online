<?php

namespace ScreepsOnline\Services;

use ScreepsOnline\Models\ServerInfo;
use ScreepsOnline\Models\ServerAvailability;

/**
 * Server Scanner Service
 *
 * Polls Screeps servers to check their availability and status
 */
class ServerScanner
{
    private ServerInfo $serverInfo;
    private ServerAvailability $serverAvailability;
    private int $timeout;

    public function __construct(int $timeout = 10)
    {
        $this->serverInfo = new ServerInfo();
        $this->serverAvailability = new ServerAvailability();
        $this->timeout = $timeout;
    }

    /**
     * Scan a single server
     *
     * @param int $serverId
     * @param string $address Format: "host:port"
     * @return array Scan result
     */
    public function scanServer(int $serverId, string $address): array
    {
        $url = "http://{$address}/api/version/";
        $result = [
            'success' => false,
            'online' => 0,
            'version' => null,
            'players' => 0,
            'error' => null
        ];

        try {
            $data = $this->fetchUrl($url);

            if ($data === false) {
                $result['error'] = 'Connection timeout or failed';
                $result['online'] = 2; // Offline
            } else {
                $json = json_decode($data, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($json['packageVersion'])) {
                    $result['success'] = true;
                    $result['online'] = 1; // Online
                    $result['version'] = $json['packageVersion'] ?? '';
                    $result['players'] = $json['users'] ?? 0;
                } else {
                    $result['error'] = 'Invalid JSON response';
                    $result['online'] = 2; // Offline
                }
            }
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $result['online'] = 2;
        }

        // Update database
        $this->updateDatabase($serverId, $result);

        return $result;
    }

    /**
     * Fetch URL with timeout
     *
     * @param string $url
     * @return string|false Response body or false on failure
     */
    private function fetchUrl(string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Screeps servers often use self-signed certs

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            logMessage("CURL error for {$url}: {$error}", 'WARNING');
            return false;
        }

        return $response;
    }

    /**
     * Update database with scan results
     *
     * @param int $serverId
     * @param array $result
     */
    private function updateDatabase(int $serverId, array $result): void
    {
        // Update server_info
        $this->serverInfo->updateInfo($serverId, [
            'version' => $result['version'] ?? '',
            'players_current' => $result['players'] ?? 0,
            'online' => $result['online']
        ]);

        // Update availability stats
        $this->serverAvailability->incrementCheck($serverId, $result['success']);

        // Calculate and update availability percentage
        $availability = $this->serverAvailability->calculateAvailability($serverId);
        $this->serverInfo->updateAvailability($serverId, $availability);
    }

    /**
     * Verify server ownership by checking welcomeText
     *
     * @param string $address Server address
     * @param string $expectedToken Token that should be in welcomeText
     * @return bool
     */
    public function verifyOwnership(string $address, string $expectedToken): bool
    {
        $url = "http://{$address}/api/user/world-start-room";

        try {
            $data = $this->fetchUrl($url);

            if ($data === false) {
                return false;
            }

            $json = json_decode($data, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            // Check if token exists in welcome text
            $welcomeText = $json['welcomeText'] ?? '';
            return strpos($welcomeText, $expectedToken) !== false;

        } catch (\Exception $e) {
            logMessage("Ownership verification failed for {$address}: " . $e->getMessage(), 'WARNING');
            return false;
        }
    }
}
