<?php

namespace ScreepsOnline\Controllers;

use ScreepsOnline\Models\Server;

/**
 * Home Controller
 *
 * Handles homepage and general pages
 */
class HomeController extends BaseController
{
    private Server $serverModel;

    public function __construct()
    {
        parent::__construct();
        $this->serverModel = new Server();
    }

    /**
     * Homepage - show recent servers (last 3 days)
     */
    public function index(): void
    {
        $servers = $this->serverModel->getAll(3);

        $this->render('home/index', [
            'servers' => $servers,
            'page_title' => 'Screeps Private Server List'
        ]);
    }

    /**
     * Show all servers (no time limit)
     */
    public function allServers(): void
    {
        $servers = $this->serverModel->getAll();

        $this->render('home/all', [
            'servers' => $servers,
            'page_title' => 'All Servers'
        ]);
    }

    /**
     * About page
     */
    public function about(): void
    {
        $this->render('home/about', [
            'page_title' => 'About'
        ]);
    }
}
