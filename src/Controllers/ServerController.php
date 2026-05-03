<?php

namespace ScreepsOnline\Controllers;

use ScreepsOnline\Models\Server;
use ScreepsOnline\Models\ServerInfo;
use ScreepsOnline\Services\ServerScanner;

/**
 * Server Controller
 *
 * Handles server-related actions
 */
class ServerController extends BaseController
{
    private Server $serverModel;
    private ServerInfo $serverInfoModel;

    public function __construct()
    {
        parent::__construct();
        $this->serverModel = new Server();
        $this->serverInfoModel = new ServerInfo();
    }

    /**
     * Show server details
     */
    public function show(): void
    {
        $address = $this->get('server');

        if (!$address) {
            $this->redirectWithError('Server address not specified', '/');
            return;
        }

        $server = $this->serverModel->getByAddress($address);

        if (!$server) {
            $this->notFound();
            return;
        }

        // Parse address into host and port
        $parts = explode(':', $address);
        $host = $parts[0] ?? '';
        $port = $parts[1] ?? '';

        $this->render('server/show', [
            'server' => $server,
            'host' => $host,
            'port' => $port,
            'page_title' => $server['name'] ?: $address
        ]);
    }

    /**
     * Show add server form
     */
    public function add(): void
    {
        $this->render('server/add', [
            'page_title' => 'Add Server'
        ]);
    }

    /**
     * Process server addition
     */
    public function store(): void
    {
        $address = trim($this->post('server', ''));

        // Validate server address
        $validatedAddress = $this->validator->validateServerAddress($address);

        if (!$validatedAddress) {
            $this->redirectWithErrors($this->validator->getErrors(), '/add');
            return;
        }

        // Check if server already exists
        if ($this->serverModel->addressExists($address)) {
            $this->redirectWithError('This server is already in the list', '/add');
            return;
        }

        // Add server
        try {
            $this->serverModel->create($address);
            $this->redirectWithSuccess('Server added successfully!', '/');
        } catch (\Exception $e) {
            logMessage('Failed to add server: ' . $e->getMessage(), 'ERROR');
            $this->redirectWithError('Failed to add server. Please try again.', '/add');
        }
    }

    /**
     * Show claim server form
     */
    public function claimForm(): void
    {
        $address = $this->get('server');

        if (!$address) {
            $this->redirectWithError('Server address not specified', '/');
            return;
        }

        $server = $this->serverModel->getByAddress($address);

        if (!$server) {
            $this->notFound();
            return;
        }

        // Check if already claimed
        if ($server['user_id']) {
            $this->redirectWithError('This server is already claimed', "/server?server={$address}");
            return;
        }

        // Generate claim token
        if (!isset($_SESSION['claim_token'])) {
            $_SESSION['claim_token'] = bin2hex(random_bytes(16));
        }

        $this->render('server/claim', [
            'server' => $server,
            'claim_token' => $_SESSION['claim_token'],
            'page_title' => 'Claim Server'
        ]);
    }

    /**
     * Process server claim
     */
    public function claim(): void
    {
        $address = $this->post('server');

        if (!$address) {
            $this->redirectWithError('Server address not specified', '/');
            return;
        }

        $server = $this->serverModel->getByAddress($address);

        if (!$server) {
            $this->notFound();
            return;
        }

        // Rate limiting - max 10 claim attempts per session
        if (!isset($_SESSION['claim_attempts'])) {
            $_SESSION['claim_attempts'] = 0;
        }

        if ($_SESSION['claim_attempts'] >= 10) {
            $this->redirectWithError('Too many claim attempts. Please try again later.', "/server?server={$address}");
            return;
        }

        $_SESSION['claim_attempts']++;

        // Check if already claimed
        if ($server['user_id']) {
            $this->redirectWithError('This server is already claimed', "/server?server={$address}");
            return;
        }

        // Verify ownership by checking server's welcomeText
        $expectedToken = $_SESSION['claim_token'] ?? '';
        $scanner = new ServerScanner();

        if (!$scanner->verifyOwnership($address, $expectedToken)) {
            $this->redirectWithError(
                'Verification failed. Make sure you added the claim token to your server\'s welcomeText.',
                "/server/claim?server={$address}"
            );
            return;
        }

        // Claim the server
        $userId = $this->auth->getCurrentUserId();
        $success = $this->serverInfoModel->claimServer($address, $userId);

        if ($success) {
            // Clear claim session data
            unset($_SESSION['claim_token']);
            unset($_SESSION['claim_attempts']);

            $this->redirectWithSuccess('Server claimed successfully!', "/server?server={$address}");
        } else {
            $this->redirectWithError('Failed to claim server. It may have been claimed by someone else.', "/server?server={$address}");
        }
    }

    /**
     * Show edit server form
     */
    public function edit(): void
    {
        $address = $this->get('server');

        if (!$address) {
            $this->redirectWithError('Server address not specified', '/');
            return;
        }

        $server = $this->serverModel->getByAddress($address);

        if (!$server) {
            $this->notFound();
            return;
        }

        // Check ownership
        $userId = $this->auth->getCurrentUserId();
        if (!$this->serverInfoModel->isOwnedBy($server['id_server'], $userId)) {
            $this->forbidden();
            return;
        }

        $this->render('server/edit', [
            'server' => $server,
            'page_title' => 'Edit Server'
        ]);
    }

    /**
     * Update server details
     */
    public function update(): void
    {
        $address = $this->post('server');

        if (!$address) {
            $this->redirectWithError('Server address not specified', '/');
            return;
        }

        $server = $this->serverModel->getByAddress($address);

        if (!$server) {
            $this->notFound();
            return;
        }

        // Check ownership
        $userId = $this->auth->getCurrentUserId();
        if (!$this->serverInfoModel->isOwnedBy($server['id_server'], $userId)) {
            $this->forbidden();
            return;
        }

        // Get and validate input
        $name = trim($this->post('name', ''));
        $description = trim($this->post('description', ''));

        // Validate
        if (!empty($name) && !$this->validator->length($name, 1, 100, 'name')) {
            $this->redirectWithErrors($this->validator->getErrors(), "/server/edit?server={$address}");
            return;
        }

        if (!empty($description) && !$this->validator->length($description, 0, 1000, 'description')) {
            $this->redirectWithErrors($this->validator->getErrors(), "/server/edit?server={$address}");
            return;
        }

        // Update server
        try {
            $this->serverInfoModel->updateDetails($server['id_server'], [
                'name' => $name,
                'description' => $description
            ]);

            $this->redirectWithSuccess('Server updated successfully!', "/server?server={$address}");
        } catch (\Exception $e) {
            logMessage('Failed to update server: ' . $e->getMessage(), 'ERROR');
            $this->redirectWithError('Failed to update server. Please try again.', "/server/edit?server={$address}");
        }
    }
}
