<?php

/**
 * Application Routes
 *
 * Define all application routes here
 */

use ScreepsOnline\Middleware\AuthMiddleware;
use ScreepsOnline\Middleware\GuestMiddleware;

// Home Routes
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/all', 'HomeController@allServers');

// Server Routes
$router->get('/server', 'ServerController@show');
$router->get('/add', 'ServerController@add');
$router->post('/add', 'ServerController@store');
$router->get('/server/claim', 'ServerController@claimForm')->middleware(AuthMiddleware::class);
$router->post('/server/claim', 'ServerController@claim')->middleware(AuthMiddleware::class);
$router->get('/server/edit', 'ServerController@edit')->middleware(AuthMiddleware::class);
$router->post('/server/edit', 'ServerController@update')->middleware(AuthMiddleware::class);

// Account Routes
$router->get('/account', 'AccountController@profile')->middleware(AuthMiddleware::class);
$router->get('/account/login', 'AccountController@login')->middleware(GuestMiddleware::class);
$router->post('/account/login', 'AccountController@authenticate')->middleware(GuestMiddleware::class);
$router->get('/account/create', 'AccountController@register')->middleware(GuestMiddleware::class);
$router->post('/account/create', 'AccountController@store')->middleware(GuestMiddleware::class);
$router->get('/account/logout', 'AccountController@logout')->middleware(AuthMiddleware::class);
$router->get('/account/reset-password', 'AccountController@resetPasswordForm');
$router->post('/account/reset-password', 'AccountController@sendResetEmail');
$router->get('/account/reset-password/confirm', 'AccountController@resetPasswordConfirm');
$router->post('/account/reset-password/confirm', 'AccountController@resetPassword');
