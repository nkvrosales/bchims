<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');
$routes->get('logout', 'Auth::logout');

$routes->group('auth', function($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::login');
    $routes->get('logout', 'Auth::logout');
});

$routes->get('dashboard', 'Dashboard::index');
$routes->get('dashboard/audit_trail', 'Dashboard::audit_trail');
$routes->post('dashboard/log_action', 'Dashboard::log_action');

$routes->group('inventory', function($routes) {
    $routes->get('/', 'Inventory::index');
    $routes->get('create', 'Inventory::create');
    $routes->post('create', 'Inventory::create');
    $routes->get('edit/(:num)', 'Inventory::edit/$1');
    $routes->post('edit/(:num)', 'Inventory::edit/$1');
    $routes->get('delete/(:num)', 'Inventory::delete/$1');
});

$routes->group('departments', function($routes) {
    $routes->get('/', 'Departments::index');
    $routes->get('create', 'Departments::create');
    $routes->post('create', 'Departments::create');
    $routes->get('edit/(:num)', 'Departments::edit/$1');
    $routes->post('edit/(:num)', 'Departments::edit/$1');
    $routes->get('delete/(:num)', 'Departments::delete/$1');
});

$routes->group('users', function($routes) {
    $routes->get('/', 'Users::index');
    $routes->get('create', 'Users::create');
    $routes->post('create', 'Users::create');
    $routes->get('edit/(:num)', 'Users::edit/$1');
    $routes->post('edit/(:num)', 'Users::edit/$1');
    $routes->get('delete/(:num)', 'Users::delete/$1');
});
