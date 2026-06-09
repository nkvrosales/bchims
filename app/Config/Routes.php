<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login', ['filter' => 'throttle']);
$routes->get('logout', 'Auth::logout');

$routes->group('auth', function($routes) {
    $routes->get('login', 'Auth::login', ['filter' => 'throttle']);
    $routes->post('login', 'Auth::login', ['filter' => 'throttle']);
    $routes->get('logout', 'Auth::logout');
});

$routes->get('dashboard', 'Dashboard::index');
$routes->get('dashboard/audit_trail', 'Dashboard::audit_trail');
$routes->post('dashboard/log_action', 'Dashboard::log_action');
$routes->post('dashboard/archive_logs', 'Dashboard::archive_logs');
$routes->get('dashboard/download_archive/(:any)', 'Dashboard::download_archive/$1');
$routes->get('dashboard/profile', 'Dashboard::profile');
$routes->post('dashboard/profile', 'Dashboard::profile');

$routes->group('inventory', function($routes) {
    $routes->get('/', 'Inventory::index');
    $routes->get('create', 'Inventory::create');
    $routes->post('create', 'Inventory::create');
    $routes->get('edit/(:num)', 'Inventory::edit/$1');
    $routes->post('edit/(:num)', 'Inventory::edit/$1');
    $routes->get('delete/(:num)', 'Inventory::delete/$1');
    $routes->post('generate_item_code', 'Inventory::generate_item_code');
});

$routes->group('departments', function($routes) {
    $routes->get('/', 'Departments::index');
    $routes->get('create', 'Departments::create');
    $routes->post('create', 'Departments::create');
    $routes->get('edit/(:num)', 'Departments::edit/$1');
    $routes->post('edit/(:num)', 'Departments::edit/$1');
    $routes->get('archive/(:num)', 'Departments::archive/$1');
    $routes->get('restore/(:num)', 'Departments::restore/$1');
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

$routes->group('categories', function($routes) {
    $routes->get('/', 'Categories::index');
    $routes->get('create', 'Categories::create');
    $routes->post('create', 'Categories::create');
    $routes->get('edit/(:num)', 'Categories::edit/$1');
    $routes->post('edit/(:num)', 'Categories::edit/$1');
    $routes->get('archive/(:num)', 'Categories::archive/$1');
    $routes->get('restore/(:num)', 'Categories::restore/$1');
    $routes->get('delete/(:num)', 'Categories::delete/$1');
});

$routes->group('supply_requests', function($routes) {
    $routes->get('/', 'SupplyRequests::index');
    $routes->post('create', 'SupplyRequests::create');
    $routes->post('serve/(:num)', 'SupplyRequests::serve/$1');
    $routes->post('partial/(:num)', 'SupplyRequests::partial/$1');
    $routes->post('complete_partial/(:num)', 'SupplyRequests::complete_partial/$1');
    $routes->post('reject/(:num)', 'SupplyRequests::reject/$1');
    $routes->post('delete/(:num)', 'SupplyRequests::delete/$1');
    $routes->post('delete_selected', 'SupplyRequests::delete_selected');
});

$routes->get('settings', 'Dashboard::settings');
